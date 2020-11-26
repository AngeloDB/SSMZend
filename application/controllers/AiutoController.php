<?php

class AiutoController extends Zend_Controller_Action
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

    public function noteAction()
        {
        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost())
            {
            $currData = $this->_request->getPost();
            $sql = 'SELECT
                  DATE_FORMAT(aiuto.NoteTimestamp, "%d/%m/%Y %H:%i") AS DataOra,
                  aiuto.Note
                  FROM aiuto
                  WHERE aiuto.Controller = "' . $currData['controller'] . '" AND 
                  aiuto.Action = "' . $currData['action'] . '"';
            $this->view->prevNotes = $this->db->fetchAll($sql);
            
            $currForm = new Custom_XmlForm();
            $currForm->setSavingAlertCallAddonMode(false);
            $currForm->initForm($this->db, 'aiuto');
            $currForm->setCurrAction($defaultNamespace->baseUrl . '/aiuto/save-note');
            $form = $currForm->getForm();
            $formDefaults = array('Controller' => $currData['controller'], 
                                  'Action' => $currData['action']);
            $form->populate($formDefaults);
            $this->view->form = $form;
            }
        }

    public function saveNoteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $translate = Zend_Registry::get('translate');
        $request = $this->getRequest();
        $currForm = new Custom_XmlForm();
        if ($this->_request->isPost())
            {
            $currData = $this->_request->getPost();
            $currData['Conferma'] = $translate->_('Conferma');
            $currForm->initForm($this->db, 'aiuto');
            $form = $currForm->getForm();
            if($currForm->insertData($currData))
                {
                return;
                }
            }
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/aiuto/save-note');
        echo $form;
        }

    public function aiutoAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->view->aiuto = "Nessun aiuto specifico";
        }

    }
