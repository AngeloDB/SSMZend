<?php

class PassportController extends Zend_Controller_Action
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

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'passaporto', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/passport/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/passport/list');
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

    public function editvistoAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'visto', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/passport/editvisto');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
//                $this->_redirect('/passport/listvisto');
                $currPers = $defaultNamespace->currPers;
                $this->_redirect('/personale/riassunto/id/'.$currPers['idPersonale']);
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

    public function listAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'passaporti');
        $this->setTitle();
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

    public function listvistoAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currPass = $request->getParam('pass');
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'visti', 0, array('currPass' => $currPass));
        $this->setTitle();
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

    public function newvistoAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Nuovo visto';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'visto');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/passport/newvisto');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $currPers = $defaultNamespace->currPers;
                $this->_redirect('/personale/riassunto/id/'.$currPers['idPersonale']);
//                $this->_redirect('/passport/listvisto');
                }
            }
        $this->setTitle();
        $form = $currForm->getForm();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    public function delvistoAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM visti WHERE idVisto='" . $currId . "'";
        $qry = $this->db->query($sql);
        $currPers = $defaultNamespace->currPers;
        $this->_redirect('/personale/riassunto/id/'.$currPers['idPersonale']);
        // action body
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $defaultNamespace->title1 = ' - Nuovo passaporto';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'passaporto');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/passport/new');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/passport/list');
                }
            }
        $this->setTitle();
        $form = $currForm->getForm();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    public function passAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currPass = $request->getParam('pass');
        $this->findPers($currId);
        if ($currPass == '')
            {
            $this->_redirect('/passport/new/id/' . $currId);
            }
        else
            {
            $this->_redirect('/passport/edit/id/' . $currPass);
            }
        //
        }

    public function visaAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currPass = $request->getParam('pass');
        $this->findPers($currId);
        if ($currPass == '')
            {
            $this->_redirect('/personale/listvisto');
            }
        else
            {
            $this->_redirect('/passport/listvisto/pass/' . $currPass);
            }
        //
        }

    public function cropAction()
        {
        $this->view->headScript()->prependFile('/public/jcrop/js/jquery.Jcrop.js');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $uploadPath = $config->uploads->get('localPath');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findPers($currId);

        $defaultNamespace->currEdit = $currId;
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'dipfoto', $currId);

        $sql = '
        SELECT `personale`.`idPersonale` ,
            `personale`.`Nome` ,
            `personale`.`Cognome` ,
            `personale`.`Telefono` ,
            `forn_pers`.`RagSoc` ,
            SUBSTRING_INDEX( GROUP_CONCAT( allegati.StoreName ) , ",", 1 ) AS `Allegato`
        FROM `personale`
        INNER JOIN `forn_pers` ON forn_pers.idFornPers = personale.idFornPers
        INNER JOIN `tab_guests` ON tab_guests.Tablename = "personale"
        LEFT JOIN `alleguests` ON alleguests.idTab_guest = tab_guests.idTab_guest
                    AND alleguests.idGuestkey = personale.idPersonale
        LEFT JOIN `allegati` ON allegati.idAllegato = alleguests.idAllegato
        WHERE (`personale`.`idPersonale` = ' . $currId . ')
        GROUP BY `personale`.`idPersonale`';
        $result = $this->db->fetchRow($sql);
        $this->view->result = $result;
        $this->view->currId = $currId;
        $this->view->uploadPath = $uploadPath;

        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $targ_w = $_POST['w']; //180;
            $targ_h = $_POST['h']; //240;
            if (count($_FILES == 0) && ($targ_h != '' && $targ_w != ''))
                {
                $jpeg_quality = 90;
                $src = $uploadPath . '/' . $result['Allegato'];
                $img_r = imagecreatefromjpeg($src);
                $dst_r = ImageCreateTrueColor($targ_w, $targ_h);
                $val1 = imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['x1'], $_POST['y1'], $targ_w, $targ_h, $_POST['w'], $_POST['h']);
                $val2 = imagejpeg($dst_r, $src, $jpeg_quality);
                $this->_redirect('/passport/listcard/id/' . $currId);
                }
            elseif (count($_FILES > 0))
                {
                unset($formData['x1']);
                unset($formData['y1']);
                unset($formData['x2']);
                unset($formData['y2']);
                unset($formData['w']);
                unset($formData['h']);
                $currForm->saveData($formData);
                $this->_redirect('/passport/crop/id/' . $currId);
                }
            }
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/passport/crop/id/' . $currId);
        $this->setTitle();
        $form = $currForm->getForm();
        $this->view->form = $form;
        $this->view->title = $currForm->getEditTitle();
        $this->view->javascript = $currForm->getJavascript();
        }

    private function findPers($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = 'SELECT * FROM personale ' .
                    'WHERE idPersonale = "' . $currId . '"';
            $currPers = $this->db->fetchRow($sql);
            $defaultNamespace->currPers = $currPers;
            }
        }

    private function setTitle()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = $defaultNamespace->currPers['Nome'] .
                " " . $defaultNamespace->currPers['Cognome'];
        }

    }
