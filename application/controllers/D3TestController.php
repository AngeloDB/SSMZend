<?php

class D3TestController extends Zend_Controller_Action
    {

    public function init()
        {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
        }

    public function indexAction()
        {
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
        $this->view->headMeta()->appendHttpEquiv('X-UA-Compatible', 'IE=edge');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/bower_components/jquery/dist/jquery.min.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/bower_components/d3-timeline/src/d3-timeline.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/d3/d3.min.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/bower_components/d3/d3.js');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/personale_riass.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/giorni_lav_grid.css');
        // action body
        }

    public function visAction()
        {
        $this->view->headMeta()->appendHttpEquiv('X-UA-Compatible', 'IE=edge');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/bower_components/moment/min/moment.min.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/bower_components/vis/dist/vis.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/bower_components/vis/dist/vis.min.css');
        }

    }
