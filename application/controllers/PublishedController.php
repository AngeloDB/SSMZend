<?php

class PublishedController extends Zend_Controller_Action
{

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
    }

    public function indexAction()
    {
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'corsipub');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->title = $currGrid->getTitle();
        $this->view->grid = $grid->deploy();
    }

    public function listAction()
    {
        // action body
    }


}



