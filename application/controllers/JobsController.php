<?php

class JobsController extends Zend_Controller_Action
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
        $this->findReq($currId);
        $this->setReqTitle();
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'jobs');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->title = $currGrid->getTitle();
        $this->view->jsGrid = $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        $this->view->idReq = $currId;
//        $this->view->cantiere = $defaultNamespace->cantiere;
        $addLink = $currGrid->getLink();
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
        $currIdReq = $defaultNamespace->currReq['idRichiesta'];
        $this->setReqTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'job', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/jobs/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if($formData['idPersonale'] == 0)
                {
                $sql = "DELETE FROM jobs WHERE idJob='" . $formData['idJob'] . "'";
                $qry = $this->db->query($sql);
                if ($defaultNamespace->currPlanning > 0)
                    {
                    $currIdProject = $defaultNamespace->currPlanning;
                    $defaultNamespace->currPlanning = '';
                    $this->_redirect('/giorni-lav/grid/id/' . $currIdProject);
                    }
                $this->_redirect('/jobs/list/id/' . $currIdReq);
//                $this->_redirect('/jobs/delete/id/' . $formData['idRichiesta']);                
                }                    
//            if(array_key_exists('Delete', $formData) && $formData['Delete'] == 'Delete')
//                {
//                $this->_redirect('/jobs/delete/id/' . $formData['idRichiesta']);                
//                }                    
            if ($currForm->saveData($formData))
                {
                if ($defaultNamespace->currPlanning > 0)
                    {
                    $currIdProject = $defaultNamespace->currPlanning;
                    $defaultNamespace->currPlanning = '';
                    $this->_redirect('/giorni-lav/grid/id/' . $currIdProject);
                    }
                $this->_redirect('/jobs/list/id/' . $currIdReq);
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

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo lavoro';
        $this->setReqTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'job');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/jobs/new');
        $currIdReq = $defaultNamespace->currReq['idRichiesta'];
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if($formData['idPersonale'] == 0)
                {
                if ($defaultNamespace->currPlanning > 0)
                    {
                    $currIdProject = $defaultNamespace->currPlanning;
                    $defaultNamespace->currPlanning = '';
                    $this->_redirect('/giorni-lav/grid/id/' . $currIdProject);
                    }
                $this->_redirect('/jobs/list/id/' . $currIdReq);
                }                    
            if ($currId = $currForm->insertData($formData))
                {
                if ($defaultNamespace->currPlanning > 0)
                    {
                    $currIdProject = $defaultNamespace->currPlanning;
                    $defaultNamespace->currPlanning = '';
                    $this->_redirect('/giorni-lav/grid/id/' . $currIdProject);
                    }
                $this->_redirect('/jobs/list/id/' . $currIdReq);
                }
            else
                {
                $form->populate($formData);
                }
            }
        $formDefaults = array('idRichiesta' => $currIdReq);
        $form->populate($formDefaults);
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
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
//            
        }

    private function findStep($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
//        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = 'SELECT * FROM prog_steps ' .
                    'WHERE idProgStep = "' . $currId . '"';
            $currStep = $this->db->fetchRow($sql);
            $this->findProject($currStep['idProgetto']);
            $defaultNamespace->currStep = $currStep;
            }
//            
        }

    private function findReq($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
//        $defaultNamespace->currProject = '';
        $defaultNamespace->currReq = '';
        if ($currId <> '')
            {
//            $sql = 'SELECT richieste.*, CONCAT(qualifiche.NumRagg, " - ",
//                            raggruppamenti.Raggruppamento, " ", 
//                            SiglaDesc, " - ", Descrizione) AS Req
            $sql = 'SELECT richieste.*, CONCAT(raggruppamenti.Raggruppamento, " ", 
                            Descrizione) AS Req
                        FROM richieste 
                        LEFT JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica
                        LEFT JOIN raggruppamenti ON raggruppamenti.NumRagg = qualifiche.NumRagg
                    WHERE idRichiesta = "' . $currId . '"';
            $currReq = $this->db->fetchRow($sql);
            $this->findStep($currReq['idProgStep']);
            $defaultNamespace->currReq = $currReq;
            }
//            
        }

    private function setReqTitle()
        {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
                $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['Nome'] . " (" .
                $defaultNamespace->currProject['JobNumber'] . ") - ".
                $translate->_("Attività") . ": " . $defaultNamespace->currStep['Step'] . " : ".
                $translate->_("Qual.") . ": " . $defaultNamespace->currReq['Req'];
//                $translate->_("Fase") . ": " . $defaultNamespace->currStep['AreaShort'] . " - ".
//                $translate->_("Attività") . ": " . $defaultNamespace->currStep['Step'] . " : ".
        }

    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM jobs WHERE idJob='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/jobs/list/id/' .
                        $defaultNamespace->currReq['idRichiesta']);
        }

    }
