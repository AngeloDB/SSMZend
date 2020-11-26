<?php

class CrudController extends Zend_Controller_Action
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

    public function listAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currValue = $request->getParam('value');
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, $currId, $currValue);
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $defaultNamespace->title1 = ' - ' . $currGrid->getTitle();
//        $this->view->title = $currGrid->getTitle();
        $this->view->grid = $grid->deploy();
        $addLink = $currGrid->getLink();
        if ($addLink != '')
            {
            $this->view->addLink = '"' . $this->view->baseUrl() . $addLink . '"';
            $this->view->addLabel = $currGrid->getLinkTitle();
            }
        }

    public function editAction()
        {
        $request = $this->getRequest();
        $currEdit = $request->getParam('edt');
        $currId = $request->getParam('id');
//        $configMenu = new Zend_Config_Xml(APPLICATION_PATH .
//                        '/configs/edits/' . $currEdit . '.xml', $currEdit);
//        $currXml = $configMenu->toArray();
//        $currKey = $currXml['currkey'];
//        $currSql = $currXml['currsql'];
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currEdit = $currId;
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, $currEdit, $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/crud/list/id/' . $currEdit);
                }
            else
                {
                $form = $currForm->getForm();
                $form->populate($formData);
                $defaultNamespace->title1 = ' - ' . $currForm->getEditTitle();
                $this->view->javascript = $currForm->getJavascript();
                $this->view->form = $form;
                }
            }
        else
            {
            $form = $currForm->getForm();
            $defaultNamespace->title1 = ' - ' . $currForm->getEditTitle();
            $this->view->javascript = $currForm->getJavascript();
            $this->view->form = $form;
            }
        }

    public function viewAction()
        {
        $request = $this->getRequest();
        $currEdit = $request->getParam('edt');
        $currId = $request->getParam('id');
        $configMenu = new Zend_Config_Xml(APPLICATION_PATH .
                        '/configs/edits/' . $currEdit . '.xml', $currEdit);
        $currXml = $configMenu->toArray();
        $currKey = $currXml['currkey'];
        $currSql = $currXml['currsql'];
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currEdit = $currId;
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, $currEdit, $currId);
        if ($this->_request->isPost())
            {
            $this->_redirect('/crud/list/id/' . $currEdit);
            }
        else
            {
            $form = $currForm->setReadOnly();
            $defaultNamespace->title1 = ' - ' . $currForm->getEditTitle();
            $this->view->javascript = $currForm->getJavascript();
            $this->view->form = $form;
            }
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currEdit = $request->getParam('edt');
//        $configMenu = new Zend_Config_Xml(APPLICATION_PATH .
//                        '/configs/edits/'.$currEdit.'.xml', $currEdit);
//        $currXml = $configMenu->toArray();
//        $currKey = $currXml['currkey'];
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, $currEdit);
        $currForm->setCurrAction($defaultNamespace->baseUrl .
                '/crud/new/edt/' . $currEdit);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->insertData($formData))
                {
                $this->_redirect('/crud/list/id/' . $currEdit);
                }
            else
                {
                $form = $currForm->getForm();
                $form->populate($formData);
                $defaultNamespace->title1 = ' - ' . $currForm->getNewTitle();
                $this->view->javascript = $currForm->getJavascript();
                $this->view->form = $form;
                }
            }
        else
            {
            $form = $currForm->getForm();
            $defaultNamespace->title1 = ' - ' . $currForm->getNewTitle();
            $this->view->javascript = $currForm->getJavascript();
            $this->view->form = $form;
            }
        }

    public function choosedbAction()
        {
        $form = new Form_Choosedb();
        $currKey = "idAppalto";
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $form->setName('formChoosedb')
                ->setMethod('post')
                ->setAction($this->view->baseUrl() . '/crud/choosedb');

        //        $this->view->form = $form;
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData))
                {
                $translate = Zend_Registry::get('translate');
                $compLabel = $translate->_('Conferma');
                if ($formData['Conferma'] == $compLabel)
                    {
                    $this->_redirect('/crud/choosetables/db/' . $formData[$Database]);
                    }
                }
            }
        else
            {

            $this->view->headLink()->appendStylesheet($this->view->baseUrl() .
                    '/styles/styles.css');
            $this->view->form = $form;
            }
        }

    public function choosetablesAction()
        {
        $request = $this->getRequest();
        $currDb = $request->getParam('db');
        $db = Zend_Registry::get('db');

        $alias = '';

        if ($this->_request->isPost())
            {
            $translate = Zend_Registry::get('translate');
            $compSalva = $translate->_('Salva');
            $compLabel = $translate->_('Conferma');
            $formData = $this->_request->getPost();
            $resSelect = json_decode($formData['joins'], true);
            if (array_key_exists('Salva', $formData) &&
                    ($formData['Salva'] == $compSalva))
                {
                $this->_redirect('/crud/choosetables/db/' . $formData[$Database]);
                }
            $found = false;
            if (array_key_exists('Conferma', $formData) &&
                    ($formData['Conferma'] == $compLabel))
                {
                foreach ($resSelect as $key => $currTable)
                    {
                    if (($formData['Alias'] == $currTable['Alias']) ||
                            ($currTable['Alias'] == ''))
                        {
                        //                        var_dump($formData, $resSelect);
                        $resSelect[$key]['Alias'] = $formData['Alias'];
                        $resSelect[$key]['Join'] = $formData['Join'];
                        $resSelect[$key]['Table'] = $formData['Table'];
                        $resSelect[$key]['On'] = $formData['On'];
                        $found = true;
                        break;
                        }
                    }
                if (!$found)
                    {
                    $resSelect[] = array('Alias' => $formData['Alias'],
                        'Join' => $formData['Join'],
                        'Table' => $formData['Table'],
                        'On' => $formData['On']);
                    }
                }
            }
        else
            {
            $resSelect = array('0' => array(
                    'Alias' => '',
                    'Join' => '',
                    'Table' => '',
                    'On' => ''
                    ));
            }

        $form = new Form_Query();
        $form->setName('formChoosetables')
                ->setMethod('post')
                ->setAction($this->view->baseUrl() . '/crud/choosetables');
        $formFields = new Form_Queryfields();
        $formFields->setName('formChoosetabfields')
                ->setMethod('post')
                ->setAction('javascript: test;');
        $elemFields = $formFields->getElement('Fields');
        foreach ($resSelect as $key => $currTable)
            {
            if ($currTable['Table'] != '')
                {
                $sql = 'SHOW COLUMNS FROM ' . $currTable['Table'];
                $result = $this->db->fetchAll($sql);
                foreach ($result as $selValue)
                    {
                    $tmp = $currTable['Table'] . '.' . $selValue['Field'];
                    $elemFields->addMultiOption($tmp, $tmp);
                    }
                }
            }
        $config = new Zend_Config_Ini(APPLICATION_PATH .
                        '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $config);
        $grid->setExport(array('pdf', 'word', 'excel', 'print'));
        $grid->addTemplateDir('My/Template/Table', 'My_Template_Table', 'table');
        $grid->addFormatterDir('My/Formatter', 'My_Formatter');
        $grid->setImagesUrl($this->getRequest()->getBaseUrl() . '/images/');
        $grid->setEscapeOutput(false);
        //$grid->setUseKeyEventsOnFilters(true);
        //        $grid->setAlwaysShowOrderArrows(true);
        $grid->setNoFilters(1);
        //        $grid->setNoOrder(0);

        $title = 'Tabelle';
        $grid->setParam('deploy.pdf.title', $title)
                ->setParam('deploy.pdf.subtitle', '')
                ->setParam('deploy.print.title', $title);

        $grid->setSource(new Bvb_Grid_Source_Array($resSelect));
        $editCol = new Bvb_Grid_Extra_Column();
        $editCol->position('left')
                ->name('Edit')
                ->title('Edit')
                ->decorator('<a href="
        javascript: submitform(\'{{Alias}}\',\'{{Join}}\',\'{{Table}}\',\'{{On}}\')"><img src="' .
                        $this->view->baseUrl() .
                        '/images/edit.png" alt="Edit" border="0"></a>');
        $grid->addExtraColumns($editCol);
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->headScript()->appendFile($this->view->baseUrl() .
                '/js/json_parse.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() .
                '/js/choosetables.js');
        $this->view->title = 'Scegli tabelle';
        $this->view->grid = $grid->deploy();
        $this->view->addLink =
                '"' . $this->view->baseUrl() . '/crud/choosetables' . '"';
        $this->view->addLabel = 'Aggiungi tabella';
        $form->joins->setValue(json_encode($resSelect));
        $this->view->form = $form;
        $this->view->formFields = $formFields;
        }

    public function choosefieldsAction()
        {
        $request = $this->getRequest();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            var_dump($formData);
            //            if ($formData['Conferma'] == 'Conferma')
            }
        }

    public function fieldsAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $joins = $this->_getParam('joins');
        $allTables = json_decode($joins, true);
        $outFields = array();
        foreach ($allTables as $key => $currTable)
            {
            $sql = 'SHOW COLUMNS FROM ' . $currTable['Table'];
            $result = $this->db->fetchAll($sql);
            foreach ($resSelect as $selValue)
                {
                $outFields[] = $currTable['Table'] . '.' . $selValue['Field'];
                }
            }
        echo json_encode(array(array('id' => 'fields',
                'payload' => $outFields)));
        exit;
        }

    }

