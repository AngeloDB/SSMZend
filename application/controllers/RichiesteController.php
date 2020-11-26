<?php

class RichiesteController extends Zend_Controller_Action
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

    public function listAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currPlanning = '';
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findStep($currId);
        $this->setStepTitle();
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'richieste');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->title = $currGrid->getTitle();
        $this->view->currProject = $defaultNamespace->currProject['idProgetto'];
        $this->view->jsGrid = $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        $this->view->cantiere = $defaultNamespace->cantiere;
        $addLink = $currGrid->getLink();
        if ($addLink != '')
            {
            $this->view->addLink = $this->view->baseUrl() . $addLink;
            $this->view->addLabel = $currGrid->getLinkTitle();
            }
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuova richiesta di personale';
        $this->setStepTitle();
//        $request = $this->getRequest();
//        $currId = $request->getParam('id');
//        if($currId > 0)
//            {
//            $this->setStepTitle();
//            }
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'richiesta');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/richieste/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $formData['idProgetto'] = $defaultNamespace->currProject['idProgetto'];
            $formData['idProgStep'] = $defaultNamespace->currStep['idProgStep'];
            if ($currId = $currForm->insertData($formData))
                {
                if ($defaultNamespace->currPlanning > 0)
                    {
                    $currIdProject = $defaultNamespace->currPlanning;
                    $defaultNamespace->currPlanning = '';
                    $this->_redirect('/giorni-lav/grid/id/' . $currIdProject);
                    }
                $this->_redirect('/richieste/list/id/' .
                        $defaultNamespace->currStep['idProgStep']);
                }
            else
                {
                $form->populate($formData);
                }
            }
        $formDefaults = array(
            'idProgetto' => $defaultNamespace->currProject['idProgetto'],
            'idProgStep' => $defaultNamespace->currStep['idProgStep']);
        $form->populate($formDefaults);
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/js/jquery_form.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/js/jquery.populate.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() . 
//            '/jquery-ui-m/src/jquery.multiselect.filter.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() . 
//            '/jquery-ui-m/assets/prettify.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() . 
//            '/jquery-ui-m/src/jquery.multiselect.js');            
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//            '/multiselect/js/plugins/localisation/jquery.localisation-min.js');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/styles.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . 
//                '/jquery-ui-m/jquery.multiselect.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . 
//                '/jquery-ui-m/assets/prettify.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . 
//                '/jquery-ui-m/jquery.multiselect.filter.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/styles.css');
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->setStepTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'richiesta', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/richieste/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                if ($defaultNamespace->currPlanning > 0)
                    {
                    $currIdProject = $defaultNamespace->currPlanning;
                    $defaultNamespace->currPlanning = '';
                    $this->_redirect('/giorni-lav/grid/id/' . $currIdProject);
                    }
                $this->_redirect('/richieste/list/id/' .
                        $defaultNamespace->currStep['idProgStep']);
                }
            else
                {
                $form->populate($formData);
                }
            }
        $formDefaults = array(
            'idProgetto' => $defaultNamespace->currProject['idProgetto'],
            'idProgStep' => $defaultNamespace->currStep['idProgStep']);
        $form->populate($formDefaults);
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/styles.css');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/js/jquery_form.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/js/jquery.populate.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() . 
//            '/jquery-ui-m/src/jquery.multiselect.filter.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() . 
//            '/jquery-ui-m/assets/prettify.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() . 
//            '/jquery-ui-m/src/jquery.multiselect.js');            
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//            '/multiselect/js/plugins/localisation/jquery.localisation-min.js');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/styles.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . 
//                '/jquery-ui-m/jquery.multiselect.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . 
//                '/jquery-ui-m/assets/prettify.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . 
//                '/jquery-ui-m/jquery.multiselect.filter.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/styles.css');
        }

    private function findProject($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = 'SELECT * FROM progetti ' .
                    'WHERE idProgetto = "' . $currId . '"';
            $currProject = $this->db->fetchRow($sql);
            $defaultNamespace->currProject = $currProject;
            }
        }

    private function setProjTitle()
        {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
                $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['Nome'] . " (" .
                $defaultNamespace->currProject['JobNumber'] . ")";
        }

    private function findStep($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = 'SELECT prog_steps.*, aree.Area, aree.AreaShort FROM prog_steps 
                    JOIN aree ON aree.idArea = prog_steps.idArea
                    WHERE idProgStep = "' . $currId . '"';
            $currStep = $this->db->fetchRow($sql);
            $this->findProject($currStep['idProgetto']);
            $defaultNamespace->currStep = $currStep;
            }
        }

    private function setStepTitle()
        {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
                $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['Nome'] . " (" .
                $defaultNamespace->currProject['JobNumber'] . ") - ".
                $translate->_("Fase") . ": " . $defaultNamespace->currStep['AreaShort'] . " - ".
                $translate->_("Attività") . ": " . $defaultNamespace->currStep['Step'];
        }

    public function printAction()
        {
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $myPdf = new Custom_Print();
        $mpdf = $myPdf->getMpdf();
        $config = Zend_Registry::get('config');
        $localPath = $config->downloads->get('localPath');
        $css = file_get_contents($localPath . '/../css/pdf.css');
        $mpdf->WriteHTML($css, 1);
        $css = file_get_contents($localPath . '/../css/printjs.css');
        $mpdf->WriteHTML($css, 1);
//        $footherTitle = 'Report di evento';
//        $mpdf->SetFooter('{DATE j-m-Y}|' . $footherTitle . '|{PAGENO}');
        $mpdf->AddPage();

        $select = $this->db->select()->from(array('A' => 'rde'))
                ->join(array('B' => 'cantieri'), 'B.idCantiere = A.idCantiere', array('Cantiere'))
                ->joinLeft(array('C' => 'aree'), 'C.idArea = A.idArea', array('Area' => 'Descrizione'))
                ->join(array('D' => 'imprese'), 'D.idImpresa = A.idImpresa', array('Appaltatrice' => 'Impresa', 'Email'))
                ->joinLeft(array('I' => 'imprese'), 'I.idImpresa = A.idImpEsecutrice', array('Esecutrice' => 'Impresa'))
                ->joinLeft(array('E' => 'utenti'), 'E.id_utente = A.idTecnico', array('Tecnico' => 'CONCAT(Nome, " ", Cognome)'))
                ->joinLeft(array('F' => 'opere'), 'F.idOpera = A.idOpera', array('Opera' => 'Lavoro'))
                ->joinLeft(array('G' => 'tab_tipirde'), 'G.idTiprde = A.Tipoevento', array('Tipo'))
                ->joinLeft(array('H' => 'tab_tipirisrde'), 'H.idTiprisrde = A.Ripetibile', array('IsRip' => 'H.Ripetibile'))
                ->where('A.idRde="' . $currId . '"');
        $qry = $this->db->query($select);
        $reqlist = $qry->fetchAll();
//        $request=$qry->fetchRow();
        $request = $reqlist[0];
        $myPdf->calcPub($this->db, $request['idPublisher'], $request['Protocollo']);
        $this->view->Intestazione = $myPdf->getIntest();
        $this->view->Firma = $myPdf->getFirma();
        $this->view->Foother = $myPdf->getFoot();
        $tmpHtml = $this->view->render('rde/print.phtml');
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
        $pages['main1'] = $myPdf->substPar($pages['main1'], $request);
        $mpdf->WriteHTML($pages['main1']);
        $mpdf->SetHTMLFooterByName('Footer2');

        $tabGuestKey = $this->findTableGuest('rde');
        if ($tabGuestKey != '')
            {
            $guestFiles = $this->findGuestFiles($tabGuestKey, $currId);
            if (is_array($guestFiles) && count($guestFiles) > 0)
                {
                $mpdf->AddPage();
                $pages['alleg_title'] = $myPdf->substPar($pages['alleg_title'], $request);
                $mpdf->WriteHTML($pages['alleg_title']);
                foreach ($guestFiles as $key => $currFile)
                    {
                    $pages['alleg_line'] = $myPdf->substPar($pages['alleg_line'], $currFile);
                    $mpdf->WriteHTML($pages['alleg_line']);
                    }
                $pages['alleg_end'] = $myPdf->substPar($pages['alleg_end'], $request);
                $mpdf->WriteHTML($pages['alleg_end']);
                }
            }
        $PdfRender = $mpdf->Output('rde_' . $currId . '.pdf', 'S');
        $this->getResponse()
                ->setHeader('Content-type', 'application/pdf')
                ->setHeader('Pragma', 'public') // required
                ->setHeader('Expires', '0')
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->setHeader('Cache-Control', 'private', false) // required for certain browsers
                ->setHeader('Content-Type', '$ctype')
//header("Content-Disposition: attachment; filename=".basename($outputFile).";" );
                ->setHeader('Content-Transfer-Encoding', 'binary')
//           ->setHeader('Content-Length', filesize($sourceFile))
                ->setBody($PdfRender);
        }

    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM richieste WHERE idRichiesta='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/richieste/list/id/' .
                        $defaultNamespace->currStep['idProgStep']);
        }

    }
