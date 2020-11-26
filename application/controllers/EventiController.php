<?php

class EventiController extends Zend_Controller_Action
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
        $this->findProject($currId);
        $this->setTitle();
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'eventi');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->currId = $currId;
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

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo evento';
        $currIdProject = $defaultNamespace->currProject['idProgetto'];
        $this->setTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'evento');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/eventi/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
//            $formData['Evento'] = nl2br($formData['Evento']);
//            $formData['Note'] = nl2br($formData['Note']);
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/eventi/list/id/'.$currIdProject);
                }
            else
                {
                $form->populate($formData);
                }
            }
        $formDefaults = array('idProgetto' => $currIdProject);
        $form->populate($formDefaults);
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currIdProject = $defaultNamespace->currProject['idProgetto'];
        $this->setTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'evento', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/eventi/edit/id/'.$currId);
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
//            $formData['Evento'] = nl2br($formData['Evento']);
//            $formData['Note'] = nl2br($formData['Note']);
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/eventi/list/id/'.$currIdProject);
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
            $sql = 'SELECT progetti.*, contratti.Sigla FROM progetti '.
                    'LEFT JOIN contratti ON contratti.idContratto = progetti.PWMainContractor ' .
                    'WHERE idProgetto = "' . $currId . '"';
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
                $defaultNamespace->currProject['Nome'] . " (" .
                $defaultNamespace->currProject['JobNumber'] . ") " .
                $defaultNamespace->currProject['Sigla'] . " - Rev." .
                $defaultNamespace->currProject['Revisione'];
        }

    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM eventi WHERE idEvento='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/eventi/list/id/' .
                $defaultNamespace->currProject['idProgetto']);
        // action body
        }

    public function formatDateAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        return "Formatted";
        }

    }
