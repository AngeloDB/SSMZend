<?php

class ClientiController extends Zend_Controller_Action
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
        $translate = Zend_Registry::get('translate');
        $defaultNamespace->cant2 = $translate->_("Clienti");
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'clienti');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
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
        $this->view->printLink = $this->view->baseUrl() . '/clienti/print';
        $this->view->printLabel = "Print";
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo cliente';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'cliente');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/clienti/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId=$currForm->insertData($formData))
                {
                $this->_redirect('/clienti/list');
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

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'cliente', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/clienti/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if($currForm->saveData($formData))
                {
                      $this->_redirect('/clienti/list');
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

    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM clienti WHERE idCliente='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/clienti/list');
        // action body
        }
        
    public function printAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4-L',
            'autoPageBreak' => true,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);
//        $localPath = $config->downloads->get('localPath');
//        echo APPLICATION_PATH . '/../public/styles/styles.css';
//        die;
        $css = file_get_contents(APPLICATION_PATH . '/../public/styles/styles.css');
        $mpdf->WriteHTML($css, 1);
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'clienti');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/styles.css');
        $html = '<div>'.$grid->deploy().'</div>';
//        $html = '<bookmark content="Start of the Document" /><div>Section 1 text</div>';
        $mpdf->WriteHTML($html);
        $PdfRender = $mpdf->Output('Clienti.pdf', 'S');
        $this->getResponse()
           ->setHeader('Content-type', 'application/pdf')
           ->setHeader('Pragma', 'public') // required
           ->setHeader('Expires', '0')
           ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
           ->setHeader('Cache-Control', 'private',false) // required for certain browsers
           ->setHeader('Content-Type', '$ctype')
//header("Content-Disposition: attachment; filename=".basename($outputFile).";" );
           ->setHeader('Content-Transfer-Encoding', 'binary')
//          ->setHeader('Content-Length', filesize($sourceFile))
           ->setBody( $PdfRender ); 
        }
        
        
    public function printOldAction()
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

        $select = $this->db->select()->from(array('clienti' => 'clienti'), 
                array('clienti.idCliente', 
                    'clienti.Nome', 
                    'clienti.RagSoc', 
                    'clienti.Citta', 
                    'clienti.Indirizzo', 
                    'clienti.Responsabile', 
                    'clienti.Telefono', 
                    'clienti.Email' ))
                ->where('clienti.idCliente="' . $currId . '"');
        $qry = $this->db->query($select);
        $reqlist = $qry->fetchAll();
//        $request=$qry->fetchRow();
        $request = $reqlist[0];
//        $myPdf->calcPub($this->db, $request['idPublisher'], $request['Protocollo']);
        $this->view->Intestazione = $myPdf->getIntest();
        $this->view->Firma = $myPdf->getFirma();
        $this->view->Foother = $myPdf->getFoot();
        $tmpHtml = $this->view->render('clienti/print.phtml');
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

//        $tabGuestKey = $this->findTableGuest('rde');
//        if ($tabGuestKey != '')
//            {
//            $guestFiles = $this->findGuestFiles($tabGuestKey, $currId);
//            if (is_array($guestFiles) && count($guestFiles) > 0)
//                {
//                $mpdf->AddPage();
//                $pages['alleg_title'] = $myPdf->substPar($pages['alleg_title'], $request);
//                $mpdf->WriteHTML($pages['alleg_title']);
//                foreach ($guestFiles as $key => $currFile)
//                    {
//                    $pages['alleg_line'] = $myPdf->substPar($pages['alleg_line'], $currFile);
//                    $mpdf->WriteHTML($pages['alleg_line']);
//                    }
//                $pages['alleg_end'] = $myPdf->substPar($pages['alleg_end'], $request);
//                $mpdf->WriteHTML($pages['alleg_end']);
//                }
//            }
        $PdfRender = $mpdf->Output('rde_'.$currId.'.pdf', 'S');
        $this->getResponse()
           ->setHeader('Content-type', 'application/pdf')
           ->setHeader('Pragma', 'public') // required
           ->setHeader('Expires', '0')
           ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
           ->setHeader('Cache-Control', 'private',false) // required for certain browsers
           ->setHeader('Content-Type', '$ctype')
//header("Content-Disposition: attachment; filename=".basename($outputFile).";" );
           ->setHeader('Content-Transfer-Encoding', 'binary')
//           ->setHeader('Content-Length', filesize($sourceFile))
           ->setBody( $PdfRender ); 
        }

    }

