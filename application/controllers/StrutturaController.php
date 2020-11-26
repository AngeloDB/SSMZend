<?php

class StrutturaController extends Zend_Controller_Action
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

    //      Progetti


    public function listAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'prog_template');
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
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo modello di progetto';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'prog_template');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/struttura/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/struttura/list');
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
        $currForm->initForm($this->db, 'prog_template', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/struttura/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/struttura/list');
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

    //   Steps


    public function listStepsAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $this->setProjTitle();
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'prog_steps_template');
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
        }

    public function newStepAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuova richiesta di personale';
        $currIdProject = $defaultNamespace->currProject['idProgTemplate'];
        $this->setProjTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'prog_step_template');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/struttura/new-step');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/struttura/list-steps' . $currIdProject);
                }
            }
        $form = $currForm->getForm();
        $formDefaults = array('idProgTemplate' => $currIdProject);
        $form->populate($formDefaults);
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    public function editStepAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currIdProject = $defaultNamespace->currProject['idProgTemplate'];
        $this->setProjTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'prog_step_template', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/struttura/edit-step');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/struttura/list-steps' . $currIdProject);
                }
            }
        $form = $currForm->getForm();
        $formDefaults = array('idProgTemplate' => $currIdProject);
        $form->populate($formDefaults);
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        }

    //    Requests


    public function listReqAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findStep($currId);
        $this->setStepTitle();
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'req_template');
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
        }

    public function newReqAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuova richiesta di personale per modello';
        $currIdProjStep = $defaultNamespace->currStep['idProgStepTemplate'];
        $this->setStepTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'req_template');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/struttura/new-req');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/struttura/list-req' . $currIdProjStep);
                }
            }
        $form = $currForm->getForm();
        $formDefaults = array(
            'idProgTemplate' => $defaultNamespace->currProject['idProgTemplate'],
            'idProgStepTemplate' => $defaultNamespace->currStep['idProgStepTemplate']);
        $form->populate($formDefaults);
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    public function editReqAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currIdProjStep = $defaultNamespace->currStep['idProgStepTemplate'];
        $this->setProjTitle();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'req_template', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/struttura/edit-req');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/struttura/list-req' . $currIdProjStep);
                }
            }
        $form = $currForm->getForm();
        $formDefaults = array(
            'idProgTemplate' => $defaultNamespace->currProject['idProgTemplate'],
            'idProgStepTemplate' => $defaultNamespace->currStep['idProgStepTemplate']);
        $form->populate($formDefaults);
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
            $sql = 'SELECT * FROM prog_template ' .
                    'WHERE idProgTemplate = "' . $currId . '"';
            $currProject = $this->db->fetchRow($sql);
            $defaultNamespace->currProject = $currProject;
            }
        }

    private function setProjTitle()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = "Progetto " . $defaultNamespace->currProject['Nome'];
//        $defaultNamespace->cantiere = $config->cantiere->get('nome') .
//                " " . $config->cantiere->get('sottotitolo') .
//                " - Progetto " . $defaultNamespace->currProject['Nome'];
        }

    private function findStep($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
//        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = 'SELECT * FROM prog_steps_template ' .
                    'WHERE idProgStepTemplate = "' . $currId . '"';
            $currStep = $this->db->fetchRow($sql);
            $this->findProject($currStep['idProgTemplate']);
            $defaultNamespace->currStep = $currStep;
            }
        }

    private function setStepTitle()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = "Progetto " . $defaultNamespace->currProject['Nome'] .
                " : Step " . $defaultNamespace->currStep['Step'];
//        $defaultNamespace->cantiere = $config->cantiere->get('nome') .
//                " " . $config->cantiere->get('sottotitolo') .
//                " - Progetto " . $defaultNamespace->currProject['Nome'] .
//                " : Step " . $defaultNamespace->currStep['Step'];
        }

    }
