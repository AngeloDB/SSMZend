<?php

class SpeseMeseController extends Zend_Controller_Action
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
        $currYM = $request->getParam('ym');
        $defaultNamespace->origYM = $currYM;
        $currRic = $request->getParam('ric');
        $defaultNamespace->origRic = $currRic;
        $annoMese = explode('-', $currYM);
        $currSpesaPar = array('idProgetto' => $currId, 'Anno' => $annoMese[1],
            'Mese' => $annoMese[0], 'idRichiesta' => $currRic);
        if (!($currRic > 0))
            {
            $currSpesaPar['idTipoSpesa'] = 6;
            }
        $defaultNamespace->currSpesaPar = $currSpesaPar;
        $this->findProject($currId);
        $this->findReq($currRic);
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'spesemese');
        $grid->updateColumn('Pers',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'Pers LIKE CONCAT("%",{{value}},"%")'));
        $grid->updateColumn('Periodo',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'Periodo LIKE CONCAT("%",{{value}},"%")'));
        $grid->updateColumn('YM',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'YM LIKE CONCAT("%",{{value}},"%")'));
        $grid->updateColumn('Tipo',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'Tipo LIKE CONCAT("%",{{value}},"%")'));
        $this->setTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->title = $currGrid->getTitle();
        $this->view->jsGrid = $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        $this->view->currId = $currId;
        $addLink = $currGrid->getLink() . '/id/' . $currId . '/ym/' . $currYM . '/ric/' . $currRic;
        if ($addLink != '')
            {
            $this->view->addLink = $this->view->baseUrl() . $addLink;
            $this->view->addLabel = $currGrid->getLinkTitle();
            }
//
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currYM = $request->getParam('ym');
        $currRic = $request->getParam('ric');
        $this->findReq($currRic);
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'spesemese', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl .
                '/spese-mese/edit/id/' . $currId . '/ym/' . $currYM . '/ric/' . $currRic);
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/spese-mese/list/id/' . $formData['idProgetto'] .
                        '/ym/' . $currYM . '/ric/' . $currRic);
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->setTitle();
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuova spesa mensile';
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $currYM = $request->getParam('ym');
        $currRic = $request->getParam('ric');
        $this->findReq($currRic);
        if ($currId > 0)
            {
            if ($currYM == '')
                {
                $annoMese = array('', '');
                }
            else
                {
                $annoMese = explode('-', $currYM);
                }
            $currPO = $this->findPO($currRic, $currYM);
            $currSpesaPar = array('idProgetto' => $currId,
                'Anno' => $annoMese[1],
                'Mese' => $annoMese[0],
                'idRichiesta' => $currRic,
                'Ordine' => $currPO);
            if (!($currRic > 0))
                {
                $currSpesaPar['idTipoSpesa'] = 6;
                }
            $defaultNamespace->currSpesaPar = $currSpesaPar;
            }
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'spesemese');
        $currForm->setCurrAction($defaultNamespace->baseUrl .
                '/spese-mese/new/id/' . $currId . '/ym/' .
                $currYM . '/ric/' . $currRic);
        $form = $currForm->getForm();
        
        $currFormWP = new Custom_XmlForm();
        $currFormWP->initForm($this->db, 'pers_forn_pers');
        $currFormWP->setCurrAction($defaultNamespace->baseUrl . '/company-rate/new');
        $formWP = $currFormWP->getForm();
        
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $flagDup = $formData['FlagDup'];
            if ($currId = $currForm->insertData($formData))
                {
                if ($flagDup == 'true')
                    {
                    $formData['idTipoSpesa'] = '';
                    $formData['DescTipoSpesa'] = '';
                    $formData['Importo'] = '';
                    $form->populate($formData);
                    }
                else
                    {
                    $this->_redirect('/spese-mese/list/id/' .
                            $formData['idProgetto'] . '/ym/' . $currYM .
                            '/ric/' . $currRic);
                    }
                }
            else
                {
                $form->populate($formData);
                }
            }
        $form->populate($defaultNamespace->currSpesaPar);
        $this->view->form = $form;
        $this->view->formWP = $formWP;
        $this->setTitle();
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/giorni_lav_grid.css');
        }

    public function newWpAction()
        {
        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(false);
        $defaultNamespace = new Zend_Session_Namespace('Default');
//        $request = $this->getRequest();
//        $currId = $request->getParam('id');
        $defaultNamespace->title1 = ' - Nuovo periodo lavorativo';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'pers_forn_pers');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/spese-mese/new-wp');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $flagDup = $formData['FlagDup'];
            if ($currId = $currForm->insertData($formData))
                {
                if ($flagDup == 'true')
                    {
                    $formData['idTipoSpesa'] = '';
                    $formData['DescTipoSpesa'] = '';
                    $formData['Importo'] = '';
                    $form->populate($formData);
                    }
                else
                    {
                    $this->_redirect('/spese-mese/list/id/' .
                            $formData['idProgetto'] . '/ym/' . $currYM .
                            '/ric/' . $currRic);
                    }
                }
            else
                {
                $form->populate($formData);
                }
            }
        $form->populate($defaultNamespace->currSpesaPar);
        $this->view->form = $form;
        $this->setTitle();
//        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-form/jquery.form.min.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jexcel/dist/js/jquery.mask.min.js');
//        ob_start();
//        echo $form;
//        $output = ob_get_clean();
//
////        $retData = "";
//        echo $this->view->render('spese-mese/new-wp.phtml');
//        exit;
        }

    public function saveWpAction()
        {
        // action body
        }

    public function listDetailAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findSM($currId);
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'spesemese_detail');
        $this->setTitle();
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
//            
        }

    public function newDetailAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuova spesa mese - dettaglio';
        $this->setTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'spesemese_dett');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/spese-mese/new-detail');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/spese-mese/list-detail');
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

    public function editDetailAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $this->setTitle();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'spesemese_dett', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/spese-mese/edit-detail');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/spese-mese/list-detail');
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
        }

    private function findProject($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = "SELECT progetti.idProgetto, progetti.Nome AS Commessa, progetti.JobNumber FROM progetti " .
                    "WHERE progetti.idProgetto = '" . $currId . "'";
            $currProject = $this->db->fetchRow($sql);
            $defaultNamespace->currProject = $currProject;
            }
//            
        }

    private function findPO($currIdReq, $currYM)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currPO = '';
        $currDate = explode('-', $currYM);
        if ($currIdReq <> '')
            {
            $sql = "SELECT pers_forn_pers.Ordine " .
                    "FROM richieste " .
                    "JOIN prog_steps ON prog_steps.idProgStep = richieste.idProgStep " .
                    "LEFT JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica " .
                    "LEFT JOIN jobs ON jobs.idRichiesta = richieste.idRichiesta " .
                    "LEFT JOIN personale ON personale.idPersonale = jobs.idPersonale " .
                    "LEFT JOIN pers_forn_pers ON pers_forn_pers.idPersonale = jobs.idPersonale AND " .
                    "pers_forn_pers.idProgetto = prog_steps.idProgetto AND " .
                    "'" . $currDate[1] . "-" . $currDate[0] . "-01' " .
                    "BETWEEN pers_forn_pers.Dal AND pers_forn_pers.Al " .
                    "WHERE richieste.idRichiesta = '" . $currIdReq . "'";
            $currPO = $this->db->fetchRow($sql);
            $defaultNamespace->currPO = $currPO;
            if (count($currPO) == 0 || $currPO['Ordine'] == '')
                {
                return "None";
                }
            else
                {
                return $currPO['Ordine'];
                }
            }
//            
        }

    private function findReq($currIdReq)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currStep = '';
        if ($currIdReq <> '')
            {
            $sql = "SELECT prog_steps.Step, aree.AreaShort, personale.Nome, personale.idPersonale, " .
                    "personale.Cognome, qualifiche.Descrizione AS Qualifica " .
                    "FROM richieste " .
                    "JOIN prog_steps ON prog_steps.idProgStep = richieste.idProgStep " .
                    "JOIN aree ON aree.idArea = prog_steps.idArea " .
                    "LEFT JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica " .
                    "LEFT JOIN jobs ON jobs.idRichiesta = richieste.idRichiesta " .
                    "LEFT JOIN personale ON personale.idPersonale = jobs.idPersonale " .
                    "WHERE richieste.idRichiesta = '" . $currIdReq . "'";
            $currStep = $this->db->fetchRow($sql);
            $defaultNamespace->currStep = $currStep;
            }
//            
        }

    private function setTitle()
        {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $tmpTitle = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['Commessa'] . " (" .
                $defaultNamespace->currProject['JobNumber'] . ")";
        if (array_key_exists('Mese', $defaultNamespace->currSpesaPar) && $defaultNamespace->currSpesaPar['Mese'] != '')
            {
            $tmpTitle .= ' - ' . $defaultNamespace->currSpesaPar['Mese'] . '/';
            }
        if (array_key_exists('Anno', $defaultNamespace->currSpesaPar) && $defaultNamespace->currSpesaPar['Anno'] != '')
            {
            if ($defaultNamespace->currSpesaPar['Mese'] == '')
                {
                $tmpTitle .= ' - ' . $translate->_("Anno") . ' ';
                }
            $tmpTitle .= $defaultNamespace->currSpesaPar['Anno'];
            }
        if (is_array($defaultNamespace->currStep))
            {
            if (array_key_exists('Step', $defaultNamespace->currStep) && $defaultNamespace->currStep['Step'] != '')
                {
                $tmpTitle .= ' - ' . $translate->_("Attività") . ' ' .
                        $defaultNamespace->currStep['Step'] . ' (' .
                        $defaultNamespace->currStep['AreaShort'] . ')';
                }
            if (array_key_exists('Qualifica', $defaultNamespace->currStep) && $defaultNamespace->currStep['Qualifica'] != '')
                {
                $tmpTitle .= ' - ' . $translate->_("Qual.") . ' ' .
                        $defaultNamespace->currStep['Qualifica'];
                }
            if (array_key_exists('Cognome', $defaultNamespace->currStep) && $defaultNamespace->currStep['Cognome'] != '')
                {
                $tmpTitle .= ' - ' . $defaultNamespace->currStep['Nome'] . ' ' .
                        $defaultNamespace->currStep['Cognome'];
                }
            }
        $defaultNamespace->cant2 = $tmpTitle;
        }

    private function findSM($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currSpesaMese = '';
        if ($currId <> '')
            {
            $sql = 'SELECT spesemese.*, progetti.Nome AS Commessa, progetti.JobNumber FROM spesemese ' .
                    'JOIN progetti ON progetti.idProgetto = spesemese.idProgetto ' .
                    'WHERE idSpeseMese = "' . $currId . '"';
            $currSM = $this->db->fetchRow($sql);
            $defaultNamespace->currSM = $currSM;
            }
//            
        }

    public function deleteAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currYM = $request->getParam('ym');
        $currRic = $request->getParam('ric');
        $sql = "DELETE FROM spesemese WHERE idSpeseMese='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/spese-mese/list/id/' .
                $defaultNamespace->currProject['idProgetto'] .
                '/ym/' . $defaultNamespace->origYM . '/ric/' . $defaultNamespace->origRic);
//                '/ym/' . $currYM . '/ric/' . $currRic);
        }

    }
