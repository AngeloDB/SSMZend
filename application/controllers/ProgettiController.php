<?php

class ProgettiController extends Zend_Controller_Action
    {

    public function init()
        {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
        }

    public function indexAction()
        {
        // action body
        }

    public function cbSemImgFilter($cbParams) {
        switch ($cbParams['value']) {
            case 'On Hold':
                $cbParams['select']->where('progetti.OnHold > 0');
                break;
            case 'Planned':
                $cbParams['select']->where('progetti.Inizio > NOW()');
                $cbParams['select']->where('progetti.OnHold = 0');
                break;
            case 'Closed':
                $cbParams['select']->where('progetti.Fine < NOW()');
                $cbParams['select']->where('progetti.OnHold = 0');
                break;
            default:
                $cbParams['select']->where('progetti.Inizio < NOW()');
                $cbParams['select']->where('progetti.Fine > NOW()');
                $cbParams['select']->where('progetti.OnHold = 0');
                break;
        }
    return $cbParams;
    }
        
        
    public function listAction()
        {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currMode = $request->getParam('mode');
        $defaultNamespace->listMode = $currMode;
        $currGrid = new Custom_XmlGrid();
        $filters = new Bvb_Grid_Filters();
        if($currMode == 'open')
            {
            $defaultNamespace->cant2 = $translate->_("Commesse aperte");
            $grid = $currGrid->getGrid($this, 'progetti_aperti');
            }
        else
            {
            $defaultNamespace->cant2 = $translate->_("Commesse");
            $grid = $currGrid->getGrid($this, 'progetti');
            $filters->addFilter('SemImg',array(
                'values' => array(
                    'On Hold' => 'On Hold',
                    'Planned' => 'Planned',
                    'Open' => 'Open',
                    'Closed' => 'Closed'),
                'callback' => array(
                    'function' => array(
                        $this,
                        'cbSemImgFilter'
                        )
                    )
                ));
            $grid->updateColumn('Semaforo',array(
                'setShowOrderImages' => 'true',
                'setNoOrder' => 'false'));
            }
        $filters->addFilter('JobNumber', array());
        $filters->addFilter('Nome', array());
        $filters->addFilter('Paese', array());
        $filters->addFilter('Indirizzo', array());
        $filters->addFilter('Contractor', array('distinct' => array(
            'field' => 'Contractor',
            'name' => 'contratti.Sigla')));
        $filters->addFilter('BU', array('distinct' => array(
            'field' => 'BU',
            'name' => 'business_units.Descrizione')));
        $filters->addFilter('ContractValue', array());
        $filters->addFilter('HqCoord', array());
        $filters->addFilter('Inizio', array()); //'render'=>'date'));
        $filters->addFilter('Fine', array()); //'render'=>'date'));
        $grid->addFilters($filters);
        $grid->updateColumn('HqCoord',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'HqCoord LIKE CONCAT("%",{{value}},"%")'));
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        if($grid->isExport())
            {
            $grid->updateColumn('Contractor',array('style'=>'color: "red";'));
            $grid->updateColumn('Fine',array('style'=>'width:40mm;'));
            }
        $this->view->title = $currGrid->getTitle();
        $this->view->jsGrid = $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        $this->view->cantiere = $defaultNamespace->cantiere;
        $addLink = $currGrid->getLink();
        $currArea = $defaultNamespace->area;
//        if ($addLink != '' && ($currArea == 'SuperIT' ||
//                               $currArea == 'ProjectOwner' || 
//                               $currArea == 'SuperLU') )
        if ($addLink != '' && ($currArea == 'SuperIT' ||
                               $currArea == 'SuperLU' ||
                               $currArea == 'Project') )
            {
            $this->view->addLink = $this->view->baseUrl() . $addLink;
            $this->view->addLabel = $currGrid->getLinkTitle();
            }
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo progetto';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'progetto');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/progetti/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $formData['Reference'] = $formData['Inizio'];
            if ($currId = $currForm->insertData($formData))
                {
                $currArea = $defaultNamespace->area;
                if($currArea == 'Project')
                    {
                    $currUser = $defaultNamespace->user;
                    $currAutor = $this->findAutor($currUser['id_utente']);
                    $this->saveGuests('autorizz_progetti', 'progetti', $currAutor, $currId);
//                    $sql = 'INSERT INTO autorizz_progetti '.
//                           '(id_utente, idProgetto, Attivo, Note) VALUES ('.
//                           '"'.$currUser['id_utente'].'",'. 
//                           '"'.$currId.'","1","Self-authorized")';
//                    $this->db->query($sql);
                    }
                $this->_redirect('/progetti/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    private function findAutor($currUser)
        {
        $findSql = 'SELECT idAutorizzProgetto FROM autorizz_progetti WHERE '.
                'id_utente = "'.$currUser.'" LIMIT 1';
        $currAutor = $this->db->fetchRow($findSql);
        if(is_array($currAutor) && $currAutor['idAutorizzProgetto']>0)
            {
            return $currAutor['idAutorizzProgetto'];
            }
        else
            {
//            $sql = 'INSERT INTO autorizz_progetti '.
//                   '(id_utente, Attivo, Note) VALUES ('.
//                   '"'.$currUser['id_utente'].'",'. 
//                   '"1","Self-authorized")';
//            $this->db->query($sql);
//            $insId = $this->db->getLastId();
            $autorTable = new Zend_Db_Table('autorizz_progetti');
            $insId = $autorTable->insert(array(
                'id_utente' => $currUser,
                'Attivo' => 1,
                'Note' => 'Self-authorized'));
            return $insId;
            }
        }
        
    private function saveGuests($currTab, $currHost, $currKey, $currSingle)
        {
        $sql = 'SELECT idTab_guest FROM tab_guests WHERE TableName = "' . $currTab . '"';
        $verbData = $this->db->fetchRow($sql);
        $currTabGuest = $verbData['idTab_guest'];
        $sql = 'SELECT idTab_host FROM tab_hosts WHERE TableName = "' . $currHost . '"';
        $verbData = $this->db->fetchRow($sql);
        $currTabHost = $verbData['idTab_host'];
        $guestHostTable = new Zend_Db_Table('guesthost');
//        $where = array($this->db->quoteInto('idTab_guest = ?', $currTabGuest),
//            $this->db->quoteInto('idTab_host = ?', $currTabHost),
//            $this->db->quoteInto('idGuest = ?', $currKey));
//        $guestHostTable->delete($where);
//        $guestHostTable->delete($guestHostTable->getAdapter()->quoteInto(
//                        'idTab_guest = "'.$currTabGuest.
//                        '" AND idTab_host = "'.$currTabHost.
//                        '" AND idGuest= "'.$currKey.'" ' 
//                        ));
//        foreach ($currMulti as $value)
//            {
        $guestHostTable->insert(array(
            'idTab_guest' => $currTabGuest,
            'idTab_host' => $currTabHost,
            'idGuest' => $currKey,
            'idHost' => $currSingle));
//            }
        }
        
        
        
    public function newContractAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo contratto PW';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'contratti_pw');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/progetti/new-contract');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/progetti/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->setTitle();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'progetto', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/progetti/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/progetti/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
//        $defaultNamespace->title1 = $title;
        }

    public function editMainContractAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $this->setTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'progetto2', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/progetti/edit-main-contract');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/progetti/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
//        $defaultNamespace->title1 = $title;
        }

    public function editContractAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $this->setTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'contratti_pw', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/progetti/edit-contract');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/progetti/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
//        $defaultNamespace->title1 = $title;
        }

    public function editBothAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $this->setTitle();

        $currTab = $request->getParam('seltab');
        if ($currTab == '')
            {
            $currTab = 0;
            }
        $defaultNamespace->currEdit = $currId;
        $this->view->currTab = $currTab;

        if($defaultNamespace->currProj1 != '')
            {
            $projForm1 = $defaultNamespace->currProj1;
            $form1 = $projForm1->getForm();
            $defaultNamespace->currProj1 = '';
            }
        else
            {
            $projForm1 = new Custom_XmlForm();
            $projForm1->initForm($this->db, 'progetto', $currId);
            $form1 = $projForm1->getForm();
            $projForm1->setCurrAction($defaultNamespace->baseUrl . '/progetti/save-proj1-riass');
            }
        $this->view->form1 = $form1;
        $this->view->js1 = $projForm1->getJavascript();

        if($defaultNamespace->currProj2 != '')
            {
            $projForm1 = $defaultNamespace->currProj2;
            $form2 = $projForm2->getForm();
            $defaultNamespace->currProj2 = '';
            }
        else
            {
            $projForm2 = new Custom_XmlForm();
            $projForm2->initForm($this->db, 'progetto2', $currId);
            $form2 = $projForm2->getForm();
            $projForm2->setCurrAction($defaultNamespace->baseUrl . '/progetti/save-proj2-riass');
            }
        $this->view->form2 = $form2;
        $this->view->js2 = $projForm2->getJavascript();

        $currGrid = new Custom_XmlGrid();
        $contrGrid = $currGrid->getGrid($this, 'pw_contracts', $currId);
        $this->view->js3 = $currGrid->getJavascript();
        $this->view->contrAddLink = $this->view->baseUrl() . $currGrid->getLink();
        $this->view->contrAddLabel = $currGrid->getLinkTitle();
        $this->view->contrGrid = $contrGrid->deploy();
        
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/jquery_form.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/jquery.populate.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m2/src/jquery.multiselect.filter.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m2/assets/prettify.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m2/src/jquery.multiselect.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/multiselect/js/plugins/localisation/jquery.localisation-min.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/personale_riass.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/assets/prettify.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.filter.css');
        }
        
    public function saveProj1RiassAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'progetto', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
//            var_dump($formData);
//            die();
            if ($currId = $currForm->saveData($formData))
                {
                $this->_redirect('/progetti/edit-both/id/' . $formData['idProgetto'] . '/seltab/0');
                }
            else
                {
                $defaultNamespace->currProj1 = $currForm;
                }    
            $this->_redirect('/progetti/edit-both/id/' . $formData['idProgetto'] . '/seltab/0');
            }    
//            
        }
        
    public function saveProj2RiassAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'progetto2', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->saveData($formData))
                {
                $this->_redirect('/progetti/edit-both/id/' . $formData['idProgetto'] . '/seltab/1');
                }
            else
                {
                $defaultNamespace->currProj2 = $currForm;
                }    
            $this->_redirect('/progetti/edit-both/id/' . $formData['idProgetto'] . '/seltab/1');
            }    
//            
        }
        
    public function ganttAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');

//            'class' => 'important',
//            'class' => 'urgent',

        $sql = 'SELECT idProgetto, ' .
                'CONCAT("<a href=\"'.$defaultNamespace->baseUrl . 
                '/prog-steps/gantt/id/", idProgetto, ' . 
                '"\">", Nome, "</A>") AS label, Inizio AS start, ' .
                'Fine as end, "" AS class FROM progetti';
        $data = $this->db->fetchAll($sql);

        setlocale(LC_ALL, 'it_IT');
        $gantti = new Custom_Gantti($data, array(
            'title' => 'Progetti',
            'cellwidth' => 25,
            'cellheight' => 35,
            'today' => true
        ));

        $this->view->gantti = $gantti;
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/gantti/styles/css/screen.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/gantti/styles/css/gantti.css');
        }

    public function printAction()
        {
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->cant2 = $translate->_("Commesse");
        $currGrid = new Custom_XmlGrid();
        $currGrid->setPrintMode(true);
        $grid = $currGrid->getGrid($this, 'progetti');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->title = $currGrid->getTitle();
        $this->view->jsGrid = $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        $this->view->cantiere = $defaultNamespace->cantiere;
        $this->view->cant2 = $defaultNamespace->cant2 . 'List';


        $myPdf = new Custom_Print();
        $mpdf = $myPdf->getMpdf();
        $mpdf->SetBasePath('http://crewplan.local');
        $config = Zend_Registry::get('config');
        $localPath = $config->downloads->get('localPath');
        $css = file_get_contents($localPath . '/../css/pdf.css');
        $mpdf->WriteHTML($css, 1);
        $css = file_get_contents($localPath . '/../css/printjs.css');
        $mpdf->WriteHTML($css, 1);
        $mpdf->AddPage();

        $select = $this->db->select()->from(array('progetti' => 'progetti'), 
                array('progetti.Nome', 'progetti.JobNumber', 
                      'progetti.Paese', 'progetti.Indirizzo'));
        $qry = $this->db->query($select);
        $reqlist = $qry->fetchAll();
//        $request=$qry->fetchRow();
        $request = $reqlist[0];
//        $myPdf->calcPub($this->db, $request['idPublisher'], $request['Protocollo']);
        $this->view->Intestazione = $myPdf->getIntest();
        $this->view->Firma = $myPdf->getFirma();
        $this->view->Foother = $myPdf->getFoot();
        $tmpHtml = $this->view->render('progetti/print.phtml');
//        $mpdf->WriteHTML($tmpHtml);

        $tmpPages = explode('#Sezione', $tmpHtml);
        $pages = array();
        foreach ($tmpPages as $page)
            {
            $page = ltrim($page);
            $sezName = strtok($page, ' ');
            $pages[$sezName] = '<!-- ' . $page;
            }
        $pages['footer1'] = $myPdf->substPar($pages['footer1'], $reqMain);
        $mpdf->DefHTMLFooterByName('Footer1', $pages['footer1']);
        $pages['footer2'] = $myPdf->substPar($pages['footer2'], $reqMain);
        $mpdf->DefHTMLFooterByName('Footer2', $pages['footer2']);
        $mpdf->SetHTMLFooterByName('Footer1');
        $pages['header1'] = $myPdf->substPar($pages['header1'], $reqMain);
        $mpdf->WriteHTML($pages['header1']);
        $pages['main1'] = $myPdf->substPar($pages['main1'], $request);
        $mpdf->WriteHTML($pages['main1']);
        $mpdf->SetHTMLFooterByName('Footer2');

        $PdfRender = $mpdf->Output('rde_' . $currId . '.pdf', 'S');
        $this->getResponse()
                ->setHeader('Content-type', 'application/pdf')
                ->setHeader('Pragma', 'public') // required
                ->setHeader('Expires', '0')
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->setHeader('Cache-Control', 'private', false) // required for certain browsers
                ->setHeader('Content-Type', '$ctype')
                ->setHeader('Content-Transfer-Encoding', 'binary')
                ->setBody($PdfRender);
        }
//
//    public function printAction()
//        {
//        $request = $this->getRequest();
//        $currId = $request->getParam('id');
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
//        $myPdf = new Custom_Print();
//        $mpdf = $myPdf->getMpdf();
//        $config = Zend_Registry::get('config');
//        $localPath = $config->downloads->get('localPath');
//        $css = file_get_contents($localPath . '/../css/pdf.css');
//        $mpdf->WriteHTML($css, 1);
//        $css = file_get_contents($localPath . '/../css/printjs.css');
//        $mpdf->WriteHTML($css, 1);
////        $footherTitle = 'Report di evento';
////        $mpdf->SetFooter('{DATE j-m-Y}|' . $footherTitle . '|{PAGENO}');
//        $mpdf->AddPage();
//
//        $select = $this->db->select()->from(array('progetti' => 'progetti'), 
//                array('progetti.Nome', 'progetti.JobNumber', 
//                      'progetti.Paese', 'progetti.Indirizzo'));
//        $qry = $this->db->query($select);
//        $reqlist = $qry->fetchAll();
////        $request=$qry->fetchRow();
//        $request = $reqlist[0];
////        $myPdf->calcPub($this->db, $request['idPublisher'], $request['Protocollo']);
//        $this->view->Intestazione = $myPdf->getIntest();
//        $this->view->Firma = $myPdf->getFirma();
//        $this->view->Foother = $myPdf->getFoot();
//        $tmpHtml = $this->view->render('progetti/print.phtml');
////        $mpdf->WriteHTML($tmpHtml);
//
//        $tmpPages = explode('#Sezione', $tmpHtml);
//        $pages = array();
//        foreach ($tmpPages as $page)
//            {
//            $page = ltrim($page);
//            $sezName = strtok($page, ' ');
//            $pages[$sezName] = '<!-- ' . $page;
//            }
//        $pages['footer1'] = $myPdf->substPar($pages['footer1'], $reqMain);
//        $mpdf->DefHTMLFooterByName('Footer1', $pages['footer1']);
//        $pages['footer2'] = $myPdf->substPar($pages['footer2'], $reqMain);
//        $mpdf->DefHTMLFooterByName('Footer2', $pages['footer2']);
//        $mpdf->SetHTMLFooterByName('Footer1');
//        $pages['header'] = $myPdf->substPar($pages['header'], $reqMain);
//        $mpdf->WriteHTML($pages['header']);
//        $pages['main1'] = $myPdf->substPar($pages['main1'], $request);
//        $mpdf->WriteHTML($pages['main1']);
//        $mpdf->SetHTMLFooterByName('Footer2');
//
////        $tabGuestKey = $this->findTableGuest('rde');
////        if ($tabGuestKey != '')
////            {
////            $guestFiles = $this->findGuestFiles($tabGuestKey, $currId);
////            if (is_array($guestFiles) && count($guestFiles) > 0)
////                {
////                $mpdf->AddPage();
////                $pages['alleg_title'] = $myPdf->substPar($pages['alleg_title'], $request);
////                $mpdf->WriteHTML($pages['alleg_title']);
////                foreach ($guestFiles as $key => $currFile)
////                    {
////                    $pages['alleg_line'] = $myPdf->substPar($pages['alleg_line'], $currFile);
////                    $mpdf->WriteHTML($pages['alleg_line']);
////                    }
////                $pages['alleg_end'] = $myPdf->substPar($pages['alleg_end'], $request);
////                $mpdf->WriteHTML($pages['alleg_end']);
////                }
////            }
//        $PdfRender = $mpdf->Output('rde_' . $currId . '.pdf', 'S');
//        $this->getResponse()
//                ->setHeader('Content-type', 'application/pdf')
//                ->setHeader('Pragma', 'public') // required
//                ->setHeader('Expires', '0')
//                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
//                ->setHeader('Cache-Control', 'private', false) // required for certain browsers
//                ->setHeader('Content-Type', '$ctype')
////header("Content-Disposition: attachment; filename=".basename($outputFile).";" );
//                ->setHeader('Content-Transfer-Encoding', 'binary')
////           ->setHeader('Content-Length', filesize($sourceFile))
//                ->setBody($PdfRender);
//        }
        
    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM progetti WHERE idProgetto='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/progetti/list');
        }
        
    public function deleteContractAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM pw_contratti WHERE idPwContratto='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/progetti/list');
        }
        
    private function findProject($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if($currId <> '')
            {
            $sql = 'SELECT * FROM progetti ' .
                   'WHERE idProgetto = "' . $currId .'"';
            $currProject = $this->db->fetchRow($sql);
            $defaultNamespace->currProject = $currProject;
            }
//
        }
        
    private function setTitle()
        {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['Nome'];
        }
        
        
        
    }
