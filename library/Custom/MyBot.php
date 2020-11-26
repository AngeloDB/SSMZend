<?php

class Custom_MyBot
    {

    private $db;

    public function init()
        {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
        }

    public function SeqRde($formData, $caller)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $db = Zend_Registry::get('db');
        $sql = 'SELECT MAX(Protocollo) AS maxnum FROM rde WHERE idCantiere =' .
                $defaultNamespace->idcantiere;
        $maxData = $db->fetchRow($sql);
        $formData['Protocollo'] = $maxData['maxnum'] + 1;
//        die ($formData['Telefono']);
        }

    public function ImpAffRuolo($formData, $caller)
        {
        if ($formData['idRuolo'] == '1')
            $formData['idImpresaSubappaltante'] = $formData['idImpresaAffidataria'] = $formData['idImpresacantiere'];
        return true;
        }

    public function AddResp($formData, $caller)
        {
        /*        $defaultNamespace = new Zend_Session_Namespace('Default');
          $db = Zend_Registry::get('db');
          $sql='SELECT idMansDip FROM tab_mansdip WHERE Mansione ="Responsabile"';
          $mansData = $db->fetchRow($sql);
          $sql= 'INSERT INTO dipendenti (idImpresa, idMansdip, Qualifica,
          Cognome, Telefono, Cellulare, Fax, Email) VALUES ("'.
          $formData['idImpresa'] . '", "'.
          $mansData['idMansdip'] . '", "Responsabile", "'.
          $formData['Responsabile'] . '", "'.
          $formData['Telefono'] . '", "'.
          $formData['Cellulare'] . '", "'.
          $formData['Fax'] . '", "'.
          $formData['Email'] . '")';
          $db->query($sql);
          $sql='SELECT idRuolodipendente FROM tab_ruolidipendenti WHERE Ruolo="Datore di lavoro"';
          $roleData = $db->fetchRow($sql);
          $insertedKey=$db->lastInsertId();
          $sql= 'INSERT INTO dipruoli (idDipendente, idRuolodipendente, idCantiere,
          Inizio) VALUES ("'.
          $insertedKey . '", "'.
          $roleData['idRuolodipendente'] . '", "'.
          $defaultNamespace->idcantiere . '", NOW())';
          $db->query($sql); */
        return true;
        }

    public function ModResp($formData, $caller)
        {
        return true;
        }

    public function Mod2Resp($formData, $caller)
        {
        if ($formData['Iva'] == '' && $formData['Codfisc'] == '')
            {
            $caller->addWarning("Occorre indicare almeno uno tra codice fiscale e partita IVA");
            return false;
            } else
            return true;
        }

    public function CheckDupUser($formData, $caller)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $db = Zend_Registry::get('db');
        $sql = 'SELECT COUNT(Protocollo) AS maxnum FROM rde WHERE idCantiere =' .
                $defaultNamespace->idcantiere;
        $maxData = $db->fetchRow($sql);
        if ($formData['Iva'] == '' && $formData['Codfisc'] == '')
            {
            $caller->addWarning("Occorre indicare almeno uno tra codice fiscale e partita IVA");
            return false;
            } else
            return true;
        }

    public function cbSemScad($listScad)
        {
        if ($listScad == '')
            return '';
        $arrScad = explode(',', $listScad);
        $today = new DateTime('now');
        $retVal = '';
        foreach ($arrScad as $key => $value)
            {
            if ($retVal != '')
                $retVal .= '<br>';
            $scadDay = new DateTime(trim($value));
            $toScad = ($scadDay->format('U') - $today->format('U')) / 86400; //$scadDay->diff($today)->days;
            if ($scadDay <= $today)
                {
                $retVal.='<span style="background-color:Magenta">';
                } elseif ($toScad < 30)
                {
                $retVal.='<span style="background-color:Yellow">';
                } else
                {
                $retVal.='<span style="background-color:GreenYellow">';
                }
            $retVal.=$scadDay->format('d/m/Y') . '</span>';
            }
        return $retVal;
        }
        
    public function cbCheckSE($currStatus)
        {
        if ($currStatus == '' || $currStatus == 0)
            return '';
        $retVal = '<img src="/public/images/doc_status_1.png" title="' . $currStatus . '">';
        return $retVal;
        }
        
        

    }