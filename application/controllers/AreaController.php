<?php

class AreaController extends Zend_Controller_Action
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
        $defaultNamespace->baseUrl = $this->view->baseUrl();
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $this->setTitle();
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'aree');
        $this->setTitle();
        $this->view->currId = $currId;
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

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currIdProject = $defaultNamespace->currProject['idProgetto'];
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'area', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/area/edit/id/' . $currId);
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/area/list/id/' . $currIdProject);
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->setTitle();
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currIdProject = $defaultNamespace->currProject['idProgetto'];
        $defaultNamespace->title1 = ' - Nuova area';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'area');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/area/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/area/list/id/' . $currIdProject);
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->setTitle();
        $nextOrd = $this->findNextOrder($currIdProject);
        $formDefaults = array('idProgetto' => $currIdProject, 'Ordine' => $nextOrd);
        $form->populate($formDefaults);
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM aree WHERE idArea='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/area/list/id/' .
                $defaultNamespace->currProject['idProgetto']);
        // action body
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

    private function findNextOrder($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        if ($currId <> '')
            {
            $sql = 'SELECT MAX(Ordine) AS currMax FROM aree ' .
                    'WHERE idProgetto = "' . $currId . '"';
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
                $defaultNamespace->currProject['JobNumber'] . ")";
        }

    }
