<?php

class ManutenzioneController extends Zend_Controller_Action
    {

    public function init()
        {
        /* Initialize action controller here */
        }

    public function indexAction()
        {
        $this->_helper->layout()->disableLayout();
        $config = Zend_Registry::get('config');
        $this->view->closedWarning = $config->startupplugin->closed->warning;
        $this->view->nomeCantiere = $config->cantiere->nome;
        }

    public function testAction()
        {
        $this->_helper->layout()->disableLayout();
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $currEdit = 'sopralluoghi';
        $fileName = APPLICATION_PATH . '/configs/edits/' . $currEdit . '.xml';
        $baseUrl = $defaultNamespace->baseUrl;
        $configMenu = new Zend_Config_Xml($fileName, $currEdit);
        print_r($configMenu);
        $object = json_decode(json_encode(simplexml_load_file($fileName)));
        echo '<br><br>';
        print_r($object);
        die;
        }

    }
    