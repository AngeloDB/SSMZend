<?php

class CashPoDetailController extends Zend_Controller_Action
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

    public function listPoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currId = $formData['id'];
            $currIdPers = $formData['idPers'];
            $currIdFornPers = $formData['idfornpers'];
            $currIdCurrFornPers = $formData['idcurrfornpers'];
            $currYM = $formData['ym'];
            $currRic = $formData['ric'];
            $currEdit = $formData['edit'];
            $annoMese = explode('-', $currYM);
            $tmp = $this->findCashPoDetail(array(
                'id' => $currId, 
                'idRic' => $currRic,
                'idPers' => $currIdPers, 
                'idFornPers' => $currIdFornPers, 
                'idCurrFornPers' => $currIdCurrFornPers, 
                'Anno' => $annoMese[1], 
                'Mese' => $annoMese[0]));
            echo json_encode(array('poTable' => $tmp));
        }
    }


    private function findCashPoTitle($currId, $currRic) {
        $sql = "SELECT 
            progetti.Nome AS Commessa,
            progetti.JobNumber,
            prog_steps.Step, 
            qualifiche.Descrizione AS Qualifica,
            forn_pers.RagSoc,
            CONCAT(personale.Nome, ' ', Cognome) AS Pers
        FROM
            richieste
        INNER JOIN
            prog_steps
        ON
            prog_steps.idProgStep = richieste.idProgStep
        INNER JOIN
            progetti
        ON
            progetti.idProgetto = prog_steps.idProgetto
        LEFT JOIN
            qualifiche
        ON
            qualifiche.idQualifica = richieste.idQualifica
        LEFT JOIN
            jobs
        ON
            jobs.idRichiesta = richieste.idRichiesta
        LEFT JOIN
            personale
        ON
            personale.idPersonale = jobs.idPersonale
        LEFT JOIN
            forn_pers
        ON
            forn_pers.idFornPers = personale.idFornPers
        WHERE
            progetti.idProgetto = '".$currId."' AND 
            richieste.idRichiesta = '".$currRic."'";

        $result = $this->db->fetchRow($sql);
        $retVal = 'Job : '.$result['Commessa'].' ('.$result['JobNumber'].')<br>'.
                  'Activity - Qual. : '.$result['Step'].' - '.$result['Qualifica'].'<br>'.
                  'Supervisor : ' . $result['Pers'] . '<br>' .
                  'Company : ' . $result['RagSoc'] . '<br>';
        return $retVal;
        }

    private function findCashPoDetail($parArr) {
        $sql = "SELECT pers_forn_pers.idPersFornPers,
                    pers_forn_pers.Dal AS Start,
                    pers_forn_pers.Al AS End,
                    pers_forn_pers.RateGG AS 'Daily Rate',
                    pers_forn_pers.PocketMoney AS 'Pocket Money',
                    pers_forn_pers.PurchaseOrder AS 'Purch. Order',
                    forn_pers.RagSoc AS Company,
                    qualifiche.Descrizione AS Qualification,
                    progetti.Nome AS Job
                FROM
                    pers_forn_pers
                LEFT JOIN
                    personale
                ON
                    personale.idPersonale = pers_forn_pers.idPersonale
                LEFT JOIN
                    forn_pers
                ON
                    forn_pers.idFornPers = pers_forn_pers.idFornPers
                LEFT JOIN
                    qualifiche
                ON
                    qualifiche.idQualifica = pers_forn_pers.idQualifica
                LEFT JOIN
                    progetti
                ON
                    progetti.idProgetto = pers_forn_pers.idProgetto
                WHERE 
                    pers_forn_pers.idProgetto = '" . $parArr['id'] . "' AND 
                    pers_forn_pers.idPersonale = '" . $parArr['idPers'] . "'";
//                    personale.Nome,
//                    personale.Cognome,
//                    ( pers_forn_pers.idPersonale = '" . $parArr['idPers'] . "' OR 
//                      pers_forn_pers.idFornPers = '" . $parArr['idFornPers'] . "' OR
//                      pers_forn_pers.idFornPers = '" . $parArr['idPersFornPers'] . "')";

        $result = $this->db->fetchAll($sql);
        $retVal = '<table id="cashPoTable" align="top" cellspacing="0" cellpadding="0">'.
                '<thead><tr>';
        $arrTitles = $result[0];
//        $currId, $currRic, $currIdPers, $anno, $mese) {
        array_shift($arrTitles);
        foreach ($arrTitles as $key => $value) {
            $retVal .= '<th>'.$key.'</th>'; 
        }
        $retVal .= '</tr></thead><tbody><tr id="poRow-0" class="gridEvid"><td colspan='.
                        count($result[0]).'>New</td></tr>';
        foreach ($result as $currLine) {
            $currId = array_shift($currLine);
            $retVal .= '<tr id="poRow-'.$currId.'">';
            foreach ($currLine as $key => $value) {
                $retVal .= '<td>'.$value.'</td>'; 
                
            }
            $retVal .= '</tr>';
        }
        $retVal .= '</tbody></table>';
        return $retVal;
    }
    
    public function getCashPoRowAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currId = $formData['id'];
            $sql = "SELECT ".
                    "pers_forn_pers.idPersFornPers, ".
                    "pers_forn_pers.idPersonale, ".
                    "pers_forn_pers.idFornPers, ".
                    "pers_forn_pers.idQualifica, ".
                    "pers_forn_pers.idProgetto, ".
                    "pers_forn_pers.Dal, ".
                    "pers_forn_pers.Al, ".
                    "pers_forn_pers.RateHH, ".
                    "pers_forn_pers.RateGG, ".
                    "pers_forn_pers.PocketMoney, ".
                    "pers_forn_pers.PurchaseOrder, ".
                    "pers_forn_pers.Ordine, ".
                    "pers_forn_pers.Note ".
                 "FROM ".
                    "pers_forn_pers ".
                 "WHERE ".
                    "pers_forn_pers.idPersFornPers = '" . $currId . "'";
            $result = $this->db->fetchRow($sql);
            echo json_encode($result);    
        }
    }

    public function saveCashPoRowAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currId = $formData['idPersFornPers'];
            if($currId > 0)
                {
                $updSql = 'UPDATE pers_forn_pers SET '. 
                    "pers_forn_pers.idPersonale = ".     
                        $this->zeroEmpty($formData['idPersonale']) .', '. 
                    "pers_forn_pers.idFornPers = ".      
                        $this->zeroEmpty($formData['idFornPers']) .', '.
                    "pers_forn_pers.idQualifica = ".     
                        $this->zeroEmpty($formData['idQualifica']) .', '.
                    "pers_forn_pers.idProgetto = ".      
                        $this->zeroEmpty($formData['idProgetto']) .', '.
                    "pers_forn_pers.Dal = ".             
                        '"'. $formData['Dal'].'", '. 
                    "pers_forn_pers.Al = ".              
                        '"'. $formData['Al'].'", '.
                    "pers_forn_pers.RateHH = ".          
                        $this->zeroEmpty($formData['RateHH']). ', '. 
                    "pers_forn_pers.RateGG = ".          
                        $this->zeroEmpty($formData['RateGG']) .', '.
                    "pers_forn_pers.PocketMoney = ".     
                        $this->zeroEmpty($formData['PocketMoney']) .', '.
                    "pers_forn_pers.PurchaseOrder = ".   
                        '"'. $formData['PurchaseOrder'].'", '.
                    "pers_forn_pers.Ordine = ".          
                        '"'. $formData['Ordine'].'", '.
                    "pers_forn_pers.Note = ".   
                        '"'. $formData['Note'].'" '.
                    'WHERE pers_forn_pers.idPersFornPers = '.$currId;    
                $this->db->query($updSql);
                }
            else 
                {
                $insSql = 'INSERT INTO pers_forn_pers ('.
                    "idPersonale, ". 
                    "idFornPers, ".      
                    "idQualifica, ".     
                    "idProgetto, ".      
                    "Dal, ".             
                    "Al, ".              
                    "RateHH, ".          
                    "RateGG, ".          
                    "PocketMoney, ".     
                    "PurchaseOrder, ".   
                    "Ordine, ".          
                    "Note ) VALUES (".             
                        $this->zeroEmpty($formData['idPersonale']).', '.
                        $this->zeroEmpty($formData['idFornPers']).', '.
                        $this->zeroEmpty($formData['idQualifica']).', '.
                        $this->zeroEmpty($formData['idProgetto']).', '.
                    '"'. $formData['Dal'].'", '.
                    '"'. $formData['Al'].'", '.
                        $this->zeroEmpty($formData['RateHH']).', '.
                        $this->zeroEmpty($formData['RateGG']).', '.
                        $this->zeroEmpty($formData['PocketMoney']).', '.
                    '"'. $formData['PurchaseOrder'].'", '.
                    '"'. $formData['Ordine'].'", '.
                    '"'. $formData['Note'].'") ';
                $this->db->query($insSql);
                }    
            }
        }

    private function zeroEmpty($value)
        {
        return empty($value) ? 0 : $value;
        }        

    public function delCashPoRowAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currId = $formData['toDel'];
            if($currId > 0)
                {
                $delSql = 'DELETE pers_forn_pers FROM pers_forn_pers '.
                    'WHERE pers_forn_pers.idPersFornPers = '.$currId;    
                $this->db->query($delSql);
                }
            }
        }


}

