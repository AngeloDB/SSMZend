<?php

class ProgStepsController extends Zend_Controller_Action
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
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currFase = $request->getParam('fase');
        $defaultNamespace->currFase = $currFase;
        $this->findProject($currId, $currFase);
        $this->setTitle();
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'prog_steps');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->currId = $currId;
        $this->view->currFase = $currFase;
        $this->view->title = $currGrid->getTitle();
        $this->view->jsGrid = $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        $this->view->cantiere = $defaultNamespace->cantiere;
        $addLink = $currGrid->getLink();
        if ($addLink != '')
            {
            $this->view->addLink = $this->view->baseUrl() . $addLink;
            $this->view->addLabel = $currGrid->getLinkTitle();
            }
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/personale_riass.css');
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo step di progetto';
        $currIdProject = $defaultNamespace->currProject['idProgetto'];
        $this->setTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'prog_step');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/prog-steps/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $this->makeRoom($formData);
            if ($currId = $currForm->insertData($formData))
                {
                $sql = 'SELECT idQualifica FROM qualifiche ' .
                   'WHERE LOWER(Descrizione) = "to be defined"';
                $currQual = $this->db->fetchRow($sql);
                $reqTable = new Zend_Db_Table('richieste');
                $reqTable->insert(array(
                    'idQualifica' => $currQual['idQualifica'],
                    'idProgStep' => $currId));

                if($defaultNamespace->currPlanning > 0)
                    {
                    $defaultNamespace->currPlanning = '';
                    $this->_redirect('/giorni-lav/grid/id/'.$currIdProject);
                    }
                $this->_redirect('/prog-steps/list/id/'.$currIdProject);
                }
            else
                {
                $form->populate($formData);
                }
            }
        $nextOrd = $this->findNextOrder($currIdProject, $defaultNamespace->currFase);
        $formDefaults = array('idProgetto' => $currIdProject, 'Ordine' => $nextOrd);
        $form->populate($formDefaults);
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $this->setTitle();
        $currId = $request->getParam('id');
        $currIdProject = $defaultNamespace->currProject['idProgetto'];
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'prog_step', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/prog-steps/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $formData = $this->makeRoomIfNeeded($formData);
            if ($currForm->saveData($formData))
                {
                if($defaultNamespace->currPlanning > 0)
                    {
                    $defaultNamespace->currPlanning = '';
                    $this->_redirect('/giorni-lav/grid/id/'.$currIdProject);
                    }
                $this->_redirect('/prog-steps/list/id/'.$currIdProject);
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
        

    public function ganttAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $this->setTitle();
//            'class' => 'important',
//            'class' => 'urgent',

        $sql = 'SELECT idProgStep, '
                . 'CONCAT("<a href=\"'.$defaultNamespace->baseUrl . 
                '/richieste/list\">", Step, "<br></A>") AS label, ' .
                'Inizio AS start, Fine as end, ' .
                '"" AS class FROM prog_steps ' .
                'WHERE prog_steps.idProgetto = "' . 
                $defaultNamespace->currProject['idProgetto'] . '"';
        $data = $this->db->fetchAll($sql);

        setlocale(LC_ALL, 'it_IT');
        $gantti = new Custom_Gantti($data, array(
            'title' => 'Steps del progetto' .
            $defaultNamespace->currProject['Nome'],
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
        
    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM prog_steps WHERE idProgStep='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/prog-steps/list/id/' .
                        $defaultNamespace->currProject['idProgetto'] . '/fase/' .
                        $defaultNamespace->currFase);
        }

    private function findProject($currId, $currArea)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if($currId <> '')
            {
            $sql = 'SELECT * FROM progetti ' .
                   'WHERE idProgetto = "' . $currId . '"';
            $currProject = $this->db->fetchRow($sql);
            $defaultNamespace->currProject = $currProject;
            $sql = 'SELECT * FROM aree ' .
                   'WHERE idArea = "' . $currArea . '"';
            $currArea = $this->db->fetchRow($sql);
            $defaultNamespace->currArea = $currArea;
            }
        }

    private function makeRoom($newRecord)
        {
        $sql = 'SELECT * FROM prog_steps ' .
               'WHERE idProgetto = "' . $newRecord['idProgetto'] . '" AND ' .
               'idArea = "' . $newRecord['idArea'] . '" AND ' .
               'Ordine = "' . $newRecord['Ordine'] . '"';
        $existingSameOrd = $this->db->fetchAll($sql);
        if(count($existingSameOrd)>0)
            {
            $sql = 'SET @row_number = '.($newRecord['Ordine']-1).'; '.
                   'UPDATE prog_steps SET '.
                   'Ordine =(@row_number := @row_number + 1) WHERE '.
                   'idProgetto = "' . $newRecord['idProgetto'] . '" AND ' .
                   'idArea = "' . $newRecord['idArea'] . '" AND ' .
                   'Ordine > "' . $newRecord['Ordine'] . '" ORDER BY Ordine';
               $this->db->query($sql); 
            }
        }

    private function makeRoomIfNeeded($currRecord)
        {
        $sql = 'SELECT * FROM prog_steps ' .
               'WHERE idProgetto = "' . $currRecord['idProgetto'] . '" AND ' .
               'idArea = "' . $currRecord['idArea'] . '" AND ' .
               'Ordine = "' . $currRecord['Ordine'] . '" AND ' .
               'idProgStep <> "' . $currRecord['idProgStep'] . '"';
        $existingSameOrd = $this->db->fetchAll($sql);
        if(count($existingSameOrd)>0)
            {
            $sql = 'UPDATE prog_steps SET '.
                   'Ordine = 999 WHERE '.
                   'idProgStep = "' . $currRecord['idProgStep'] . '";'.
                   'SET @row_number = '.($currRecord['Ordine']).'; '.
                   'UPDATE prog_steps SET '.
                   'Ordine =(@row_number := @row_number + 1) WHERE '.
                   'idProgetto = "' . $currRecord['idProgetto'] . '" AND ' .
                   'idArea = "' . $currRecord['idArea'] . '" AND ' .
                   'Ordine >= "' . $currRecord['Ordine'] . '" ORDER BY Ordine';
               $this->db->query($sql); 
            }
        return $currRecord;    
        }

    private function findNextOrder($currId, $currArea)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        if ($currId <> '')
            {
            $sql = 'SELECT MAX(Ordine) AS currMax FROM prog_steps ' .
                    'WHERE idProgetto = "' . $currId . '" AND idArea = "' . $currArea . '"';
            $currMax = $this->db->fetchRow($sql);
            return $currMax['currMax'] + 1;
            }
        //    
        }
        
    private function setTitle()
        {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
                $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['Nome'] . " (" .
                $defaultNamespace->currProject['JobNumber'] . ") " .
                $defaultNamespace->currArea['AreaShort'];
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

    }
