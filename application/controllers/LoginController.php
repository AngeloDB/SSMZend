<?php

class LoginController extends Zend_Controller_Action
    {

    public function init()
        {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
//        Zend_Dojo::enableView($this->view);
//        Zend_Dojo_View_Helper_Dojo::setUseDeclarative();
        }

    public function indexAction()
        {
        # If we're already logged in, just redirect
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        if (Zend_Auth::getInstance()->hasIdentity())
            {
            //$this->_redirect('index/index');
            }
        
        $request = $this->getRequest();
        $loginForm = new Form_Login_Index();
        $loginForm->setAction($defaultNamespace->baseUrl . '/login/index/');

        $errorMessage = "";

        if ($request->isPost())
            {
            if ($loginForm->isValid($request->getPost()))
                {

                $authAdapter = $this->getAuthAdapter();

                # get the username and password from the form
                $username = $loginForm->getValue('username');
                $password = $loginForm->getValue('password');

                # pass to the adapter the submitted username and password
                $authAdapter->setIdentity($username)
                        ->setCredential($password);
                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                # is the user a valid one?
                if ($result->isValid())
                    {
                    # all info about this user from the login table
                    # ommit only the password, we don't need that
                    $userInfo = $authAdapter->getResultRowObject(null, 'password');

                    # the default storage is a session with namespace Zend_Auth
                    $authStorage = $auth->getStorage();
                    $authStorage->write($userInfo);

                    $select = $this->db->select()
                        ->from(array('utenti' => 'utenti'), array('id_utente',
                                'id_cantiere',
                                'id_impresa',
                                'nome',
                                'cognome',
                                'interno',
                                'cellulare',
                                'email',
                                'id_userlevel',
                                'note'))
                        ->join(array('tab_userlevel' => 'tab_userlevel'), 
                                'tab_userlevel.id_userlevel = utenti.id_userlevel', 
                                array('desc_userlevel', 
                                    'desc_ul_short', 
                                    'num_userlevel',
                                    'single_cant', 
                                    'single_impresa',
                                    'titolo_userlevel' => 'Titolo'))
                        ->joinLeft(array('autorizz_progetti' => 'autorizz_progetti'),
                            'autorizz_progetti.id_utente = utenti.id_utente',
                            array('idAutorizzProgetto' => 'MIN(idAutorizzProgetto)',
                                'Attivo' => 'MIN(Attivo)'))                            
                        ->joinLeft(array('tab_guests' => 'tab_guests'),
                            'tab_guests.Tablename="autorizz_progetti"',
                            array())
                        ->joinLeft(array('tab_hosts' => 'tab_hosts'),
                            'tab_hosts.Tablename="progetti"',
                            array())
                        ->joinLeft(array('guesthost' => 'guesthost'),
                                'guesthost.idTab_guest=tab_guests.idTab_guest AND 
                                guesthost.idTab_host=tab_hosts.idTab_host AND 
                                guesthost.idGuest=autorizz_progetti.idAutorizzProgetto',
                            array())
                        ->joinLeft(array('progetti' => 'progetti'),
                            'progetti.idProgetto = guesthost.idHost',
                            array('Commesse' => 'GROUP_CONCAT(progetti.Nome SEPARATOR ", ")',
                                  'AutCommesse' => 'GROUP_CONCAT(CONCAT("#", progetti.idProgetto, "#") SEPARATOR ", ")'))
                        ->group(array('utenti.id_utente'))
                        ->where('utenti.id_utente="' . $userInfo->id_utente . '"');
//$sql = $select->__toString();
//die("$sql\n");
                    $qry = $this->db->query($select);
                    $tmp = $select->assemble();
                    $reqlist = $qry->fetchAll();
                    $request = $reqlist[0];
                    $request['intestazione'] = '';
                    $defaultNamespace = new Zend_Session_Namespace('Default');
                    $defaultNamespace->area = $request['desc_ul_short'];
//                    $defaultNamespace->area = $request['desc_userlevel'];
                    $defaultNamespace->userLevel = $request['desc_ul_short'];
                    $defaultNamespace->user = $request;
                    //if ($defaultNamespace->area == 'Superuser')
                    //{ //$defaultNamespace->area == 'Amministratore' || 
                    $this->_redirect('/');
                    //}
                    }
                else
                    {
                    $errorMessage = $translate->_("Username o password errata.");
                    }
                }
            else
                {
                $errorMessage = $translate->_("Username o password errata.");
                }
            }
        else
            {
            $config = Zend_Registry::get('config');
            $this->view->cantiere = $config->cantiere->get('nome') . " " . $config->cantiere->get('sottotitolo');
            
            $configPath = APPLICATION_PATH . '/configs/acl.xml';
            $frontend = array(
                'master_files' => array($configPath),
                'automatic_serialization' => true);
            $backend = array();
            $cache = Zend_Cache::factory('File', 'Memcached', $frontend, $backend);
            $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }
        $this->view->transTest = $translate->_('Cm');
        $this->view->errorMessage = $errorMessage;
        $this->view->loginForm = $loginForm;
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/jquery.backstretch.js');
//        $config = Zend_Registry::get('config');
//        $uploadPath = $config->uploads->get('localPath');
//        var_dump($uploadPath);
//        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/styles/styles.css');
        }

    public function logoutAction()
        {
        # clear everything - session is cleared also!
        Zend_Auth::getInstance()->clearIdentity();
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->cantiere = "";
        $defaultNamespace->area = "base";
        $this->_redirect('/login/index');
        }

    /**
     * Gets the adapter for authentication against a database table
     *
     * @return object
     *
     */
    protected function getAuthAdapter()
        {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName('login')
                ->setIdentityColumn('username')
                ->setCredentialColumn('password')
                ->setCredentialTreatment('MD5(?)');

        return $authAdapter;
        }

    public function getanagAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->_request->isPost())
            {
            $currData = $this->_request->getPost();
            $currId = $currData['idUtente'];
            $sql = 'SELECT nome, cognome, interno, cellulare, email, id_impresa
                FROM utenti
                WHERE id_utente = "' . $currId . '"';
            $result = $this->db->fetchAll($sql);
            $value = reset($result);
            $retData = array('nome' => rawurlencode($value['nome']),
                'cogn' => rawurlencode($value['cognome']),
                'tel' => rawurlencode($value['interno']),
                'cell' => rawurlencode($value['cellulare']),
                'email' => rawurlencode($value['email']),
                'impr' => rawurlencode($value['id_impresa']));
            echo json_encode($retData);
            }
        exit;
        }

    public function updateAction()
        {
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $this->view->username = $userInfo->username;
        $this->view->name = $userInfo->name;
        $this->view->email = $userInfo->email;
        $form = new Form_Login_Update();
        $currKey = "id";
        $request = $this->getRequest();
        $currId = $userInfo->id;
        $form->setName('formUpdateLogin')
                ->setMethod('post')
                ->setAction($this->view->baseUrl() . '/login/update');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData))
                {
                $translate = Zend_Registry::get('translate');
                $compLabel = $translate->_('Conferma');

                if (($formData['Conferma'] == $compLabel) &&
                        ($formData['new1_passw'] == $formData['new2_passw']))
                    {
                    $loginTable = new Zend_Db_Table('login');
                    $tableCols = $loginTable->info('cols');
                    $toDelete = array_diff_key($formData, array_flip($tableCols));
                    $formData['password'] = md5($formData['new1_passwd']);
                    foreach ($toDelete as $key => $value)
                        {
                        unset($formData[$key]);
                        }
                    unset($formData[$currKey]);
                    $where = $loginTable->getAdapter()->quoteInto($currKey . ' =? ', $currId);
                    $loginTable->update($formData, $where);
                    $this->_redirect('/index/index');
                    }
                }
            }
        else
            {
            $sql = 'SELECT * FROM login WHERE ' . $currKey . '=' . $currId;
            $arrFields = $this->db->fetchRow($sql);
            $form->populate($arrFields);
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/styles/styles.css');
            $this->view->form = $form;
            }
        }

    }
