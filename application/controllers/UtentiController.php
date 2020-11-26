<?php

class UtentiController extends Zend_Controller_Action
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
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'login');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->title = $currGrid->getTitle();
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
        $defaultNamespace->title1 = ' - Nuovo login';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'login');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/utenti/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($formData['password1'] == $formData['password2'])
                {
                $formData['password'] = md5($formData['password1']);
                $currForm->insertData($formData);
                $this->_redirect('/utenti/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();            
//        else
//            {
//            $form = $currForm->getForm();
//            $this->view->form = $form;
//            }
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currIdUtente = $request->getParam('idutente');
        $currIdLogin = $request->getParam('idlogin');
        $defaultNamespace->currEdit = $currIdLogin;
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'login', $currIdLogin);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/utenti/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($formData['password1'] == $formData['password2'])
                {
                $formData['password'] = md5($formData['password1']);
                if($formData['id']=='')
                    $currForm->insertData($formData);
                else
                    $currForm->saveData($formData);
                $this->_redirect('/utenti/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->form->getElement('id_utente')->setValue($currIdUtente);
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');

//            else
//                {
//                $form = $currForm->getForm();
//                $this->view->form = $form;
//                $this->view->title = $currForm->getEditTitle();
//                }
//            }
//        else
//            {
//            $form = $currForm->getForm();
//            $this->view->form = $form;
//            $this->view->form->getElement('id_utente')->setValue($currIdUtente);
//            $this->view->title = $currForm->getEditTitle();
//            }
        }

    }