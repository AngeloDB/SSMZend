<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
//        Zend_Dojo::enableView($this->view);
//        Zend_Dojo_View_Helper_Dojo::setUseDeclarative();
    }

    public function indexAction()
    {
//        $this->view->headLink()->appendStylesheet($this->view->baseUrl() .
//            '/styles/styles.css');
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();

        // $this->view->baseUrl() . 
        if ($userInfo == null)
            {
            $this->_redirect('/login');
            }
        else
            {
            $defaultNamespace = new Zend_Session_Namespace('Default');
            $currArea = $defaultNamespace->area;
            switch ($currArea)
                {
                case 'SuperIT':
                case 'SuperLU':
                    $this->_redirect('/index/admin');
                    break;

                case 'Superuser':
                    $this->_redirect('/index/superuser');
                    break;

                case 'Project':
                case 'Guest':
                case 'Admin':
                case 'Secretary':
                case 'Info':
                case 'User':
                    $this->_redirect('/index/admin');
                    break;

                default:
                    break;
                }
            }
        //
    }

    public function newCantieriAction()
    {
        // action body
    }

    public function editeventAction()
    {
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        header('Content-type:text/json;charset=UTF-8');
        $select = $this->db->select()->from(
                        array('A' => 'eventi'), array('id' => 'idEvento',
                    '*'))
                ->join(array('B' => 'tab_eventi'), 'B.idTipoEvento = A.idTipoEvento', array('title' => 'TipoEvento'))
                ->joinLeft(array('C' => 'cantieri'), 'C.idCantiere = A.idCantiere', array('Cantiere' => 'Cantiere'))
                ->joinLeft(array('D' => 'tecnici'), 'D.idTecnico = A.idTecnico', array('Nome' => 'Nome',
                    'Cognome' => 'Cognome'))
                ->where('A.idEvento = ?', $currId);
        $stmt = $select->query();
        $resSelect = $stmt->fetchAll();
        header('Content-Type: application/json');
        echo json_encode($resSelect);
        exit;
    }

    public function guestAction()
    {
        $this->view->title = 'Guest';
    }

    public function superuserAction()
    {
        $this->view->title = 'Superuser';
    }

    public function adminAction()
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->cant2 = "World sites";
        $currUser = $defaultNamespace->user;
        $this->view->currUser = $currUser;
        $this->view->title = 'Amministratore';
        $select1 = $this->db->select()->from(array('A' => 'locations'), 
                array('idProgetto', 'Nome', 'Latitudine', 'Longitudine'));
        $select2 = $this->db->select()->from(array('A' => 'progetti'), 
                array('idProgetto', 'Nome', 'Latitudine', 'Longitudine'));
        $sel = $this->db->select();
        $sel->union(array($select1, $select2));
        $smt = $sel->query();
        $locations = $smt->fetchAll();
        $this->view->locations = $locations;
        $attr1 = 'text/javascript';
        $attr2 = array('charset' => 'utf-8');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-mapael/js/maps/world_countries.js', $attr1, $attr2);
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-mapael/js/jquery.mapael.js', $attr1, $attr2);
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-raphael/raphael.min.js', $attr1, $attr2);
    }

    public function respAction()
    {
        $this->view->title = 'Responsabile lavori';
    }

    public function cbStartup($Doc, $disp)
    {
        if ((strpos($Doc, 'CCIAA Conforme') !== FALSE) &&
                (strpos($Doc, 'DURC Conforme') !== FALSE))
            return $disp;
        else
            return '';
    }

    public function savemoddocAction()
    {
        // action body
    }

    public function approvaimpAction()
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $mailSender = new Custom_Mail2();

// Recupera dati impresa        
        $sql = 'SELECT imprese.*, impresecantiere.idImpresacantiere,
            impresecantiere.idImpresaAffidataria, tab_ruoli.Ruolo, 
            tab_moddocimp.Modello
            FROM imprese
            JOIN impresecantiere ON impresecantiere.idImpresa=imprese.idImpresa
            LEFT OUTER JOIN tab_ruoli ON tab_ruoli.idRuolo=impresecantiere.idRuolo
            LEFT OUTER JOIN tab_moddocimp ON 
                    tab_moddocimp.idModdocimp=impresecantiere.idModdocimp
            WHERE imprese.idImpresa = "' .
                $currId . '"';
        $impData = $this->db->fetchRow($sql);
        if ($impData['Ruolo'] == 'Affidataria')
            $newMod = "Affidataria";
        else
            {
            if ($impData['Modello'] == 'Startup impresa')
                $newMod = "Esecutrice";
            else
                $newMod = "Lavoratore autonomo";
            }
//        if($impData['Ruolo']=='Subappalto 1')
//            $newMod="Esecutrice";
//        else
//            $newMod="Affidataria";
// Update impresecantieri
        $sql = 'UPDATE impresecantiere SET 
                idModdocimp = (SELECT idModdocimp FROM tab_moddocimp 
                WHERE Modello = "' . $newMod . '")
                WHERE idImpresacantiere="' . $impData['idImpresacantiere'] . '"';
        $this->db->query($sql);

// Update utenti
        $sql = 'UPDATE utenti SET id_userlevel = 
            (SELECT id_userlevel FROM tab_userlevel 
            WHERE desc_userlevel="Impresa")
        WHERE id_impresa="' . $currId . '"';
        $this->db->query($sql);

// Elimina vecchio modello doc
        $sql = 'DELETE FROM documenticantiere 
            WHERE idCantiere = "' . $defaultNamespace->user['id_cantiere'] .
                '" AND idImpresa = "' . $currId . '"';

// Aggiungi nuovo modello doc
        $sql = 'INSERT INTO documenticantiere(
            idCantiere, idImpresa, idTab_documento, Obbligatorio)
            SELECT "' . $defaultNamespace->user['id_cantiere'] . '", "' .
                $currId . '", idTab_documento, Obbligatorio 
            FROM moddoccant 
            WHERE moddoccant.idCantiere = "' .
                $defaultNamespace->user['id_cantiere'] . '" AND
                moddoccant.idModdocimp = 
                (SELECT tab_moddocimp.idModdocimp from tab_moddocimp
                    WHERE tab_moddocimp.Modello = "' . $newMod . '")';
        $this->db->query($sql);

// Recupera dati impresa        
//        $sql = 'SELECT * FROM imprese WHERE idImpresa = "'.
//                $currId . '"';
//        $impData = $this->db->fetchRow($sql);
// Recupera dati utente        
        $sql = 'SELECT * FROM utenti WHERE id_cantiere = "' .
                $defaultNamespace->user['id_cantiere'] . '"
                AND id_impresa = "' .
                $currId . '"';
        $utData = $this->db->fetchRow($sql);
        if ($utData == NULL)
            {
            $sql = 'SELECT * FROM utenti WHERE id_cantiere = "' .
                    $defaultNamespace->user['id_cantiere'] . '"
                    AND id_impresa = "' .
                    $impData['idAffidataria'] . '"';
            $utData = $this->db->fetchRow($sql);
            $currNome = $utData['nome'];
            $currCognome = $utData['cognome'];
            $currMail = $impData['Email'];
            }
        else
            {
// Crea soggetto
            $currNome = $impData['Respnome'];
            $currCognome = $impData['Respcognome'];
            $currMail = $utData['email'];
            $sql = 'INSERT INTO soggetti(idCantiere, idImpresa, idMansione, 
                Descmansione, id_utente, Nome, Cognome, Email, Attivo)
                SELECT "' . $defaultNamespace->user['id_cantiere'] . '", "' .
                    $impData['idImpresa'] . '", 
                            tab_mansioni.idMansione,  
                            tab_mansioni.Mansione, "' .
                    $utData['id_utente'] . '", "' .
                    $currNome . '", "' .
                    $currCognome . '", "' .
                    $impData['Email'] . '", "1" 
                            FROM tab_mansioni
                            WHERE tab_mansioni.Mansione="Responsabile impresa"';
            if (($retVal = $this->db->query($sql)) == false)
                {
                $ret = mysql_error();
                return;
                }
            else
                {
                $currUserId = $this->db->lastInsertId();
                }
            }
// Manda email
//                "Romeo Safety Italia srl\n\rsede: via Imperia 26-28 20142 Milano\n\rtel: 02 84.800.210 fax: 02 89.545.300\n\rC.F. e P. Iva 12689530157\n\r sito web: www.safetyitalia.it - email: servizi@safeyitalia.it";
        $mailer = new Custom_Mailer();
        $header = '';
        $message = " per l'impresa " . $impData['Impresa'] . "\n\rI documenti verificati in fase di Start-up sono risultati conformi per il cantiere " .
                $defaultNamespace->cantiere . "\n\rPertanto La Vs. impresa deve produrre la rimanente documentazione secondo quanto previsto dal Dlg 81/08 per essere autorizzato all'avvio dei lavori presso il sopracitato cantiere.\n\rLe informazioni relative alla documentazione richiesta sono visualizzate nella Vs Homepage\n\r" .
                "\n\r 1) Inserire nel browser le coordinate del sito:\n      -> " . $_SERVER['HTTP_HOST'] . "/public/" .
                "\n\r 2) Inserire i codici personali di accesso al sistema in Vs possesso" .
                "\n\r 3) Caricate la documentazione richiesta come da indicazioni presenti in homepage - (elenco documenti per VTP)" .
                "\n\rDistinti saluti\n\r" . $mailSender->getSender() .
                $oggetto = 'Impresa ' . $impData['Impresa'] .
                ' approvata per il cantiere ' . $defaultNamespace->cantiere;
        $mailer->send2(
                $currMail, $oggetto, 'Spett. ' . $currNome . ' ' .
                $currCognome . ' ' .
                $currMail .
                ",\nCantiere " . $defaultNamespace->cantiere .
                ",\nIn data " . date_format(date_create($currData['datapub']), 'd/m/Y') .
                ' ' . $message, $header);
        $this->_redirect('/index/admin');
    }

    public function updateallAction()
    {
        $sql = 'SELECT impresecantiere.idCantiere, impresecantiere.idImpresa,
            impresecantiere.idModdocimp
            FROM impresecantiere
            WHERE impresecantiere.idModdocimp IN (1,2,3)';
        $impData = $this->db->fetchAll($sql);

        foreach ($impData as $imp)
            {
            echo "Updating impresa #" . $imp['idImpresa'] . '<br />';
//            $sql = 'SELECT "' . 
//                    $imp['idCantiere'] . '", "' .
//                    $imp['idImpresa'] . '", 
//                    idTab_documento, Obbligatorio
//                FROM moddoccant 
//                WHERE moddoccant.idCantiere = "' .
//                    $imp['idCantiere']  . '" AND
//                    moddoccant.idModdocimp = "'.$imp['idModdocimp'].'" AND
//                    moddoccant.idTab_documento > 23';
//            $docData = $this->db->fetchAll($sql);
//            foreach ($docData as $doc)
//                {
//                echo "  Selected document #".$doc['idTab_documento'].'<br />';
//                }
            $sql = 'INSERT INTO documenticantiere(
                idCantiere, idImpresa, idTab_documento, Obbligatorio)
                SELECT "' .
                    $imp['idCantiere'] . '", "' .
                    $imp['idImpresa'] . '", 
                    idTab_documento, Obbligatorio
                FROM moddoccant 
                WHERE moddoccant.idCantiere = "' .
                    $imp['idCantiere'] . '" AND
                    moddoccant.idModdocimp = "' . $imp['idModdocimp'] . '" AND
                    moddoccant.idTab_documento > 23';
            $this->db->query($sql);
            }
//
    }

    public function areaAction()
    {
        // action body
    }

    public function listJobsDettAction()
    {
        // action body
    }


}


