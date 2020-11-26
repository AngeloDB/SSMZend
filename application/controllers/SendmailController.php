<?php

class SendmailController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
    }

    public function indexAction()
    {
        // action body
    }

    public function mailAction()
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'sendmail', null);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/sendmil/mail');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $mailer    = new Custom_Mailer();
            $mailer->send($formData['destinatario'], $formData['oggetto'], $formData['testo']);
            $this->view->result = "Messaggio spedito";
            $this->view->currId = $result['idDipendente'];
            $this->view->form = '';
            }
        else
            {
            $form = $currForm->getForm();
            $defaultNamespace->title1 = ' - ' . $currForm->getEditTitle();
            $this->view->javascript = $currForm->getJavascript();
            $this->view->form = $form;
            $this->view->result = '';
            }
        //  
    }


}



