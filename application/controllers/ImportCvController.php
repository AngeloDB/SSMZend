<?php

class ImportCvController extends Zend_Controller_Action
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
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->cant2 = $translate->_("Import CV");
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'new_pers');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/grid-evid.css');
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

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->title1 = ' - Import CV';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'new_pers');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/import-cv/new');
        $form = $currForm->getForm();
        $form->getElement('Accetta')->setAttribs(array('style' => 'display: none'));
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->saveGuests('new_pers', 'qualifiche', $currId, $formData['idMultiQual']);
                $this->explodeZip($currId);
                $this->_redirect('/import-cv/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/jquery_form.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/jquery.populate.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m2/src/jquery.multiselect.filter.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m2/assets/prettify.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m2/src/jquery.multiselect.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/multiselect/js/plugins/localisation/jquery.localisation-min.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/assets/prettify.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.filter.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'new_pers', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/import-cv/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->saveGuests('new_pers', 'qualifiche', $formData['idNewPers'], $formData['idMultiQual']);
                $this->_redirect('/import-cv/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
//        $form = $currForm->getForm();
        $form->getElement('idMultiQual')->setValue($this->getGuests('new_pers', 'qualifiche', $currId));
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/jquery_form.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/jquery.populate.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m/src/jquery.multiselect.filter.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m/assets/prettify.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery-ui-m/src/jquery.multiselect.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/multiselect/js/plugins/localisation/jquery.localisation-min.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m/jquery.multiselect.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m/assets/prettify.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m/jquery.multiselect.filter.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
//        $defaultNamespace->title1 = $title;
        }

    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql1 = 'DELETE guesthost '. 
                'FROM guesthost '.
                'JOIN tab_guests ON tab_guests.idTab_guest=guesthost.idTab_guest '. 
                'JOIN tab_hosts ON tab_hosts.idTab_host=guesthost.idTab_host '. 
                'WHERE guesthost.idGuest="' . $currId .
                '" AND tab_guests.TableName="new_pers"' .
                ' AND tab_hosts.TableName="qualifiche"';
        $this->db->query($sql1);
        
        $sql2 = 'SELECT '. 
                    'allegati.StoreName, '. 
                    'allegati.idAllegato, '. 
                    'alleguests.idAlleGuest '. 
                'FROM allegati '.
                'JOIN alleguests ON alleguests.idAllegato = allegati.idAllegato '. 
                'JOIN tab_guests ON tab_guests.idTab_guest=alleguests.idTab_guest '. 
                'WHERE alleguests.idGuestkey="' . $currId .
                        '" AND tab_guests.TableName="new_pers"';
        $currAll = $this->db->fetchAll($sql2);

        foreach ($currAll as $allegato)
            {
            unlink($this->view->baseUrl().'/uploads/'.$allegato['StoreName']);
            $sql3 = 'DELETE allegati FROM allegati WHERE idAllegato = "'.
                    $allegato['idAllegato'].'"';
            $this->db->query($sql3);
            $sql4 = 'DELETE alleguests FROM alleguests WHERE idAlleGuest = "'.
                    $allegato['idAlleGuest'].'"';
            $this->db->query($sql4);
            }
        $sql5 = 'DELETE new_pers FROM new_pers WHERE idNewPers = "'.$currId.'"';
        $this->db->query($sql5);
        $this->_redirect('/import-cv/list');
        // action body
        }

    private function getGuests($currTab, $currHost, $currKey)
        {
        $sql = 'SELECT guesthost.idHost AS Selected FROM guesthost
                JOIN tab_guests ON tab_guests.idTab_guest=guesthost.idTab_guest 
                JOIN tab_hosts ON tab_hosts.idTab_host=guesthost.idTab_host 
                WHERE guesthost.idGuest="' . $currKey .
                '" AND tab_guests.TableName="' . $currTab . '"' .
                ' AND tab_hosts.TableName="' . $currHost . '"';
        $selData = $this->db->fetchAll($sql);
        $selArr = array();
        foreach ($selData as $selVal)
            {
            $selArr[] = $selVal['Selected'];
            }
        return $selArr;
        }

    private function saveGuests($currTab, $currHost, $currKey, $currMulti)
        {
        $sql = 'SELECT idTab_guest FROM tab_guests WHERE TableName = "' . $currTab . '"';
        $verbData = $this->db->fetchRow($sql);
        $currTabGuest = $verbData['idTab_guest'];
        $sql = 'SELECT idTab_host FROM tab_hosts WHERE TableName = "' . $currHost . '"';
        $verbData = $this->db->fetchRow($sql);
        $currTabHost = $verbData['idTab_host'];
        $guestHostTable = new Zend_Db_Table('guesthost');
        $where = array($this->db->quoteInto('idTab_guest = ?', $currTabGuest),
            $this->db->quoteInto('idTab_host = ?', $currTabHost),
            $this->db->quoteInto('idGuest = ?', $currKey));
        $guestHostTable->delete($where);
        foreach ($currMulti as $value)
            {
            $guestHostTable->insert(array(
                'idTab_guest' => $currTabGuest,
                'idTab_host' => $currTabHost,
                'idGuest' => $currKey,
                'idHost' => $value));
            }
//
        }

    private function explodeZip($currId)
        {
        $select = $this->db->select()->from(array('new_pers' => 'new_pers'), array(
                    'idNewPers' => 'new_pers.idNewPers',
                    'Nome' => 'new_pers.Nome',
                    'Cognome' => 'new_pers.Cognome',
                    'idFornPers' => 'new_pers.idFornPers',
                    'idQualifica' => 'new_pers.idQualifica'))
                ->join(array('tab_guests' => 'tab_guests'), "tab_guests.Tablename = 'new_pers'", array())
                ->joinLeft(array('alleguests' => 'alleguests'), "alleguests.idTab_guest = tab_guests.idTab_guest AND 
                    alleguests.idGuestkey = new_pers.idNewPers", array())
                ->joinLeft(array('allegati' => 'allegati'), 'allegati.idAllegato = alleguests.idAllegato', array(
                    'idAllegato' => 'allegati.idAllegato',
                    'StoreName' => 'allegati.StoreName',
                    'Nome' => 'allegati.Nome'))
                ->where('new_pers.idNewPers="' . $currId . '"');

        $qry = $this->db->query($select);
        $reqlist = $qry->fetchAll();
        $tmpDir = $this->tempdir('uploads/tmp/');
  //      var_dump($reqlist, $tmpDir);
        $zip = new ZipArchive;
        $res = $zip->open('uploads/'.$reqlist[0]['StoreName']);
        if ($res === TRUE) 
            {        // extract it to the path we determined above
            $zip->extractTo($tmpDir);
            $zip->close();
            $errZip = $zip->getStatusString();
//            var_dump($errZip);
//            die;
            if ($dh = opendir($tmpDir))
                {
                $addArr = array(
                    'idNewPers' => $reqlist[0]['idNewPers'],
                    'Nome' => $reqlist[0]['Nome'], 
                    'idQualifica' => $reqlist[0]['idQualifica'], 
                    'idFornPers' => $reqlist[0]['idFornPers']);
                while (($file = readdir($dh)) !== false)
                    {
                    if ($file != '' && $file != '.' && $file != '..')
                        {
                        $this->addCandidate($addArr, $tmpDir, $file);
                        }
//                    echo "filename:" . $file . "<br>";
                    }
                closedir($dh);
                rmdir($tmpDir);
                }
            } 
        }
        
    private function addCandidate($addArr, $tmpDir, $currFile)
        {
        $npTable  = new Zend_Db_Table('new_pers');
        $npKey   = $npTable->insert(array(
            'Nome' => $currFile,
            'idQualifica' =>  $addArr['idQualifica'], 
            'idFornPers' =>  $addArr['idFornPers']));
        
        $tmp                  = preg_replace('/[^a-z0-9.]+/i', "_", strtolower($currFile));
        $newName              = substr(md5(microtime()), 1, 4) . $tmp;
        rename($tmpDir.'/'.$currFile, 'uploads/' . $newName);
        chmod('uploads/' . $newName, 0644);
        $allArr = array('Nome' => $currFile, 'StoreName' => $newName);
        $tabGuestKey = $this->findTableGuest('new_pers');
        $this->saveAllegato($npKey, $tabGuestKey, $allArr, 'AllPers');
        }

    private function findTableGuest($tableName)
        {
        $sql       = 'SELECT * FROM tab_guests WHERE TableName="' . $tableName . '"';
        $arrFields = $this->db->fetchRow($sql);
        return $arrFields['idTab_guest'];
        }


    private function saveAllegato($newKey, $tabGuestKey, $allArr, $guestField)
        {
        $allTable = new Zend_Db_Table('allegati');
        $allKey   = $allTable->insert($allArr);
        $agTable  = new Zend_Db_Table('alleguests');
        $allKey   = $agTable->insert(array(
            'idAllegato'  => $allKey,
            'Guest'       => $guestField, 
            'idTab_guest' => $tabGuestKey,
            'idGuestKey'  => $newKey));
        }
        
        
    private function tempdir($dir = null, $prefix = 'tmp_', $mode = 0777, $maxAttempts = 1000)
        {
        /* Use the system temp dir by default. */
        if (is_null($dir))
            {
            $dir = sys_get_temp_dir();
            }

        /* Trim trailing slashes from $dir. */
        $dir = rtrim($dir, '/');

        /* If we don't have permission to create a directory, fail, otherwise we will
         * be stuck in an endless loop.
         */
        if (!is_dir($dir) || !is_writable($dir))
            {
            return false;
            }

        /* Make sure characters in prefix are safe. */
        if (strpbrk($prefix, '\\/:*?"<>|') !== false)
            {
            return false;
            }

        /* Attempt to create a random directory until it works. Abort if we reach
         * $maxAttempts. Something screwy could be happening with the filesystem
         * and our loop could otherwise become endless.
         */
        $attempts = 0;
        do
            {
            $path = sprintf('%s/%s%s', $dir, $prefix, mt_rand(100000, mt_getrandmax()));
            }
        while (
        !mkdir($path, $mode) &&
        $attempts++ < $maxAttempts
        );

        return $path;
        }

    }
