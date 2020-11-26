<?php

class AutorizzController extends Zend_Controller_Action
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
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $defaultNamespace->cant2 = $translate->_("Autorizzazioni");
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'autorizz');
        $grid->updateColumn('utente',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'utente LIKE CONCAT("%",{{value}},"%")'));
        $grid->updateColumn('Commesse',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'Commesse LIKE CONCAT("%",{{value}},"%")'));
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
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currIdUtente = $request->getParam('idutente');
        $currId = $request->getParam('id');
        $currUtente = $this->findUser($currIdUtente);
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'autorizz', $currId, 
                array('currIdUtente' => $currIdUtente, 'currUtente' => $currUtente));
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/autorizz/edit');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($formData['idAutorizzProgetto'] != '')
                {
                if ($currForm->saveData($formData))
                    {
                    $this->saveGuests('autorizz_progetti', 'progetti', 
                            $formData['idAutorizzProgetto'], $formData['Commesse']);
                    $this->_redirect('/autorizz/list');
                    }
                else
                    {
                    $form->populate($formData);
                    }
                }
            else
                {
                if ($newId = $currForm->insertData($formData))
                    {
                    $this->saveGuests('autorizz_progetti', 'progetti', $newId, $formData['Commesse']);
                    $this->_redirect('/autorizz/list');
                    }
                else
                    {
                    $form->populate($formData);
                    }
                }
            }
        $form = $currForm->getForm();
        $form->getElement('Commesse')->setValue($this->getGuests('autorizz_progetti', 'progetti', $currId));
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
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
        }

    private function findUser($currId)
        {
        if ($currId <> '')
            {
            $sql = 'SELECT CONCAT(nome, " ", cognome) AS Utente FROM utenti ' .
                    'WHERE id_utente = "' . $currId . '"';
            $currUser = $this->db->fetchRow($sql);
            return $currUser['Utente'];
            }
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
//        $guestHostTable->delete($guestHostTable->getAdapter()->quoteInto(
//                        'idTab_guest = "'.$currTabGuest.
//                        '" AND idTab_host = "'.$currTabHost.
//                        '" AND idGuest= "'.$currKey.'" ' 
//                        ));
        foreach ($currMulti as $value)
            {
            $guestHostTable->insert(array(
                'idTab_guest' => $currTabGuest,
                'idTab_host' => $currTabHost,
                'idGuest' => $currKey,
                'idHost' => $value));
            }
        }

    public function swapActiveAction()
        {
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currIdUtente = $request->getParam('idutente');
        $currActive = $request->getParam('attivo');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if($currActive == '1')
            {
            $updSql= 'UPDATE autorizz_progetti SET Attivo = "0" WHERE id_utente="'. $currIdUtente .'"';
            }
        else
            {
            $updSql= 'UPDATE autorizz_progetti SET Attivo = "1" WHERE id_utente="'. $currIdUtente .'"';
            }    
        $this->db->query($updSql);
        $this->_redirect('/autorizz/list');
        }

    }
