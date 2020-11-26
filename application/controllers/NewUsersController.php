<?php

class NewUsersController extends Zend_Controller_Action
    {

    public function init()
        {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
        /* Initialize action controller here */
        }

    public function indexAction()
        {
        // action body
        }

    public function listAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $defaultNamespace->cant2 = $translate->_("Utenti");
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'utenti_new');
        $grid->updateColumn('Username',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'Username LIKE CONCAT("%",{{value}},"%")'));
        $grid->updateColumn('Tipo',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'tab_userlevel.Titolo LIKE CONCAT("%",{{value}},"%")'));
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
        // action body
        }

    public function editAction()
        {
        // action body
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'utenti', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/new-users/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/new-users/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->findUser($currId);
        $this->setTitle();
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        }

    public function newAction()
        {
        // action body
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo utente';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'utenti');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/new-users/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/new-users/edit-password/idutente/' . $currId . '/idlogin/');
//                $this->_redirect('/new-users/list');
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

    public function editPasswordAction()
        {
        // action body
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currIdUtente = $request->getParam('idutente');
        $currIdLogin = $request->getParam('idlogin');
        $defaultNamespace->currEdit = $currIdLogin;
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'login', $currIdLogin);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/new-users/edit-password');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($formData['password1'] == $formData['password2'])
                {
                $formData['password'] = md5($formData['password1']);
                if ($formData['id'] == '')
                    $currForm->insertData($formData);
                else
                    $currForm->saveData($formData);
                $this->_redirect('/new-users/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->findUser($currIdUtente);
        $this->setTitle();
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->form->getElement('id_utente')->setValue($currIdUtente);
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        }

    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM login WHERE id_utente='" . $currId . "'";
        $qry = $this->db->query($sql);
        $sql2 = "DELETE FROM utenti WHERE id_utente='" . $currId . "'";
        $qry2 = $this->db->query($sql2);
        $this->_redirect('/new-users/list');
        // action body
        }

    private function findUser($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currUser = '';
        if ($currId <> '')
            {
            $sql = 'SELECT utenti.*,  tab_userlevel.Titolo AS Tipo FROM utenti ' .
                    'LEFT JOIN tab_userlevel ON tab_userlevel.id_userlevel = utenti.id_userlevel ' .
                    'WHERE utenti.id_utente = "' . $currId . '"';
            $currUser = $this->db->fetchRow($sql);
            $defaultNamespace->currUser = $currUser;
            }
//            
        }

    private function setTitle()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = $defaultNamespace->currUser['nome'] .
                " " . $defaultNamespace->currUser['cognome'] .
                ' - ' . $defaultNamespace->currUser['Tipo'];
        }

    }
