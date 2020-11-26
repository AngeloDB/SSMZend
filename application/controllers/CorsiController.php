<?php

class CorsiController extends Zend_Controller_Action
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

    public function importAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $config = Zend_Registry::get('config');
        $registry = Zend_Registry::getInstance();
        $uploadPath = $config->uploads->get('localPath');

        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'importfile', 0);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/corsi/import');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();

//            if ($currForm->validateForm($formData))
//                {
            /* Uploading Document File on Server */
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->setDestination($uploadPath);
            try
                {
// upload received file(s)
                $upload->receive();
                }
            catch (Zend_File_Transfer_Exception $e)
                {
                $e->getMessage();
                }
            $uploadedData = $currForm->getFormValues();
            $name = $upload->getFileName();
            $size = $upload->getFileSize();
            $mimeType = $upload->getMimeType();
            $completename = str_replace('\\', '/', $name);
            $handle = fopen($completename, "r");
            $arrCsv = array();
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
                {
                $arrCsv[] = $data;
                }
            $listFields = str_replace('DataVera', 'DataCorso', implode(',', $arrCsv[0]));
            for ($i = 1; $i < count($arrCsv); $i++)
                {
                $sql = 'REPLACE INTO calcorsi(' . $listFields .
                        ') VALUES ("' . implode('","', $arrCsv[$i]) . '")';
                $qry = $this->db->query($sql);
                }
            fclose($handle);
            }
//            }
        else
            {
            $form = $currForm->getForm();
            $this->view->form = $form;
            $this->view->title = $currForm->getEditTitle();
            }
//            
        }

    public function exportAction()
        {
// action body
        }

    public function listAction()
        {
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'sceltacorsi');
//        var_dump($currGrid);
//        var_dump($currGrid->getJavascript());
//        die;
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->title = $currGrid->getTitle();
        $this->view->javascript = $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        }

    public function savepubbAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $currPubb = $formData['pubb'];
            if ($currPubb)
                {
                $currPubb = 0;
                }
            else
                {
                $currPubb = 1;
                }
            $sql = 'UPDATE calcorsi SET Pubblicato="' . $currPubb . '"' .
                    ' WHERE ID_Corso="' . $formData['idcorso'] . '"';
            $this->db->query($sql);
            exit;
            }
        }

    public function impcorsistiAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $config = Zend_Registry::get('config');
        $registry = Zend_Registry::getInstance();
        $uploadPath = $config->uploads->get('localPath');

        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'importfile', 0);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/corsi/impcorsisti');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();

//            if ($currForm->validateForm($formData))
//                {
            /* Uploading Document File on Server */
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->setDestination($uploadPath);
            try
                {
// upload received file(s)
                $upload->receive();
                }
            catch (Zend_File_Transfer_Exception $e)
                {
                $e->getMessage();
                }
            $uploadedData = $currForm->getFormValues();
            $name = $upload->getFileName();
            $size = $upload->getFileSize();
            $mimeType = $upload->getMimeType();
            $completename = str_replace('\\', '/', $name);
            //$xml = new Zend_Config_Xml($completename, $completename);
            $xml = simplexml_load_file($completename);
            $arrRep = json_decode(json_encode($xml), true);
            $this->tableReplace($arrRep, "corsi");
            $this->tableReplace($arrRep, "corsisti");
            $this->tableReplace($arrRep, "imprese");
//            die;
            }
        else
            {
            $form = $currForm->getForm();
            $this->view->form = $form;
            $this->view->titleOss = "File corsisti confermati da RF-PRO";
            //        $currForm->getEditTitle();
            }
        }

    private function tableReplace($arr, $tab)
        {
//        print_r($tab);
        if (!is_array($arr))
            return;
        if (!is_array($arr[$tab]))
            return;
        $currArr = $arr[$tab];
//        if (!is_array($currArr[0]))
//            return;
        $firstChild = reset($currArr);
//        echo " ";
//        print_r($firstChild);
        if (is_array($firstChild))
            $ff = reset($firstChild);
        if (is_array($ff))
            {
            $listFields = implode(',', array_keys($ff));
            foreach ($firstChild as $currRec)
                {
                $listValues = str_replace('"Array"', '""', '"' . implode('","', $currRec) . '"');
                $this->tRep($tab, $listFields, $listValues);
                }
            }
        else   // unico record
            {
            $listFields = implode(',', array_keys($firstChild));
            $listValues = str_replace('"Array"', '""', '"' . implode('","', $firstChild) . '"');
            $this->tRep($tab, $listFields, $listValues);
            }
        }

    private function tRep($tab, $fields, $values)
        {
        $sql = 'REPLACE INTO ' . $tab . ' (' . $fields .
                ') VALUES (' . $values . ')';
//        var_dump($sql);  // $tab, $fields, $values, 
        $qry = $this->db->query($sql);
        }

    private function SimpleXML2Array($xml)
        {

        $array = (array) $xml;

        if (count($array) == 0)
            {
            $array = (string) $xml;
            }

        if (is_array($array))
            {
            //recursive Parser
            foreach ($array as $key => $value)
                {
                if (is_object($value))
                    {
                    if (strpos(get_class($value), "SimpleXML") !== false)
                        {
                        $array[$key] = $this->SimpleXML2Array($value);
                        }
                    }
                else
                    {
                    $array[$key] = $this->SimpleXML2Array($value);
                    }
                }
            }
        return $array;
        }

    public function impattAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $config = Zend_Registry::get('config');
        $registry = Zend_Registry::getInstance();
        $uploadPath = $config->uploads->get('localPath');

        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'importfile', 0);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/corsi/impatt');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();

//            if ($currForm->validateForm($formData))
//                {
            /* Uploading Document File on Server */
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->setDestination($uploadPath);
            try
                {
// upload received file(s)
                $upload->receive();
                }
            catch (Zend_File_Transfer_Exception $e)
                {
                $e->getMessage();
                }
            $uploadedData = $currForm->getFormValues();
            $name = $upload->getFileName();
            $size = $upload->getFileSize();
            $mimeType = $upload->getMimeType();
            $completename = str_replace('\\', '/', $name);
            print_r($uploadedData);
            var_dump($name, $completename);

            $zip = new ZipArchive();
            print_r($zip);
            $x = $zip->open($completename);
            if ($x === true)
                {
                $zip->extractTo($uploadPath . "/attestati/"); // change this to the correct site path
                $zip->close();
                }
            die;
            }
        else
            {
            $form = $currForm->getForm();
            $this->view->form = $form;
            $this->view->title = $currForm->getEditTitle();
            }
        }

    }
