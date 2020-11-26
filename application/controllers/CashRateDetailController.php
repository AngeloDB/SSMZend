<?php

class CashRateDetailController extends Zend_Controller_Action
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

    public function listRatesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currId = $formData['id'];
            $currYM = $formData['ym'];
            $currRic = $formData['ric'];
            $currEdit = $formData['edit'];
            $annoMese = explode('-', $currYM);
            $currTitleArr = $this->findCashTitle($currId, $currRic);
            $currDetailArr = $this->findCashDetail(
                        $currId, $currRic, $annoMese[1], $annoMese[0]);
            $retArr = array(
                'TitleArr'  => $currTitleArr,
                'Title'     => $this->formatCashTitle($currTitleArr),
                'Date'      => 'Date : '.$annoMese[0].'/'.$annoMese[1].' ',
                'DetailArr' => $currDetailArr, 
                'Detail'    => $this->formatCashdetail($currDetailArr)
                );
//            echo $this->findCashTitle($currId, $currRic);
//            echo 'Date : '.$annoMese[0].'/'.$annoMese[1].'<br>';    
//            echo $this->findCashDetail($currId, $currRic, $annoMese[1], $annoMese[0]);
            echo json_encode($retArr);
        }
    }

    private function findCashTitle($currId, $currRic) {
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
        return $result;
        }

    private function formatCashTitle($result) {        
        $retVal = 'Job : '.$result['Commessa'].' ('.$result['JobNumber'].')  '.
                  'Activity - Qual. : '.$result['Step'].' - '.$result['Qualifica'].'<br>'.
                  'Supervisor : ' . $result['Pers'] . ' ' .
                  'Company : ' . $result['RagSoc'] . '<br>';
        return $retVal;
        }

    private function findCashDetail($currId, $currRic, $anno, $mese) {
        $sql = "SELECT 
                spesemese.idSpeseMese,
                spesemese.Importo AS Amount,
                spesemese.Storno AS Balance,
                spesemese.Fattura AS Invoice,
                spesemese.Ordine AS 'P.O.',
                CONCAT(
                    tab_tipospesa.Descrizione,
                    IF(
                        tab_tipospesa.Descrizione = 'Others',
                        CONCAT(' (', spesemese.DescTipoSpesa, ')'),
                        ''
                    )
                ) AS 'Cost Type'
            FROM
                spesemese
            LEFT JOIN
                tab_tipospesa
            ON
                tab_tipospesa.idTabTipoSpesa = spesemese.idTipoSpesa
            INNER JOIN
                progetti
            ON
                progetti.idProgetto = spesemese.idProgetto
            LEFT JOIN
                `richieste`
            ON
                richieste.idRichiesta = spesemese.idRichiesta
            WHERE
                spesemese.idProgetto = '" . $currId . "' AND 
                spesemese.Anno = '" . $anno . "' AND 
                spesemese.Mese = '" . $mese . "' AND
                spesemese.idRichiesta = '" . $currRic . "'";

        $result = $this->db->fetchAll($sql);
        return $result;
        }
        
    private function formatCashdetail($result) {        
        $retVal = '<table id="cashDetTable" align="top" cellspacing="0" cellpadding="0">'.
                '<thead><tr>';
        $arrTitles = $result[0];
        array_shift($arrTitles);
        foreach ($arrTitles as $key => $value) {
            $retVal .= '<th>'.$key.'</th>'; 
        }
        $retVal .= '</tr></thead><tbody><tr id="cdRow-0" class="gridEvid"><td colspan='.
                        count($result[0]).'>New</td></tr>';
        foreach ($result as $currLine) {
            $currId = array_shift($currLine);
            $retVal .= '<tr id="cdRow-'.$currId.'">';
            foreach ($currLine as $key => $value) {
                $retVal .= '<td>'.$value.'</td>'; 
                
            }
            $retVal .= '</tr>';
        }
        $retVal .= '</tbody></table>';
        return $retVal;
    }
    
    public function getCashRowAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currId = $formData['id'];
            $sql = "SELECT ".
                    "spesemese.idSpeseMese, ".
                    "spesemese.idTipoSpesa, ".
                    "spesemese.DescTipoSpesa, ".
                    "spesemese.Importo, ".
                    "spesemese.Storno, ".
                    "spesemese.Fattura, ".
                    "spesemese.Ordine ".
                 "FROM ".
                    "spesemese ".
                 "WHERE ".
                    "spesemese.idSpeseMese = '" . $currId . "'";
            $result = $this->db->fetchRow($sql);
            echo json_encode($result);    
        }
    }

    public function saveCashRowAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currId = $formData['idSpeseMese'];
            if($currId > 0)
                {
                $updSql = 'UPDATE spesemese SET '.
                    'spesemese.idTipoSpesa = '.    $this->zeroEmpty($formData['idTipoSpesa']).', '.
                    'spesemese.DescTipoSpesa = "'. $formData['DescTipoSpesa'].'", '.
                    'spesemese.Importo = '.        $this->zeroEmpty($formData['Importo']).', '.
                    'spesemese.Storno = '.         $this->zeroEmpty($formData['Storno']).', '.
                    'spesemese.Fattura = "'.       $formData['Fattura'].'", '.
                    'spesemese.Ordine = "'.        $formData['Ordine'].'" '.
                    'WHERE spesemese.idSpeseMese = '.$currId;    
                $this->db->query($updSql);
                }
            else 
                {
                $insSql = 'INSERT INTO spesemese ('.
                    'idProgetto, '.   
                    'idRichiesta, '.   
                    'Anno, '.   
                    'Mese, '.   
                    'idTipoSpesa, '.
                    'DescTipoSpesa, '.
                    'Importo, '.
                    'Storno, '.
                    'Fattura, '.
                    'Ordine ) VALUES ('.
                         $this->zeroEmpty($formData['idProgetto']).', '.
                         $this->zeroEmpty($formData['idRichiesta']).', '.
                    '"'. $formData['Anno'].'", '.
                    '"'. $formData['Mese'].'", '.
                         $this->zeroEmpty($formData['idTipoSpesa']).', '.
                    '"'. $formData['DescTipoSpesa'].'", '.
                         $this->zeroEmpty($formData['Importo']).', '.
                         $this->zeroEmpty($formData['Storno']).', '.
                    '"'. $formData['Fattura'].'", '.
                    '"'. $formData['Ordine'].'") ';    
                $this->db->query($insSql);
                }    
            }
        }

    private function zeroEmpty($value)
        {
        return empty($value) ? 0 : $value;
        }        

    public function delCashRowAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currId = $formData['toDel'];
            if($currId > 0)
                {
                $delSql = 'DELETE spesemese FROM spesemese '.
                    'WHERE spesemese.idSpeseMese = '.$currId;    
                $this->db->query($delSql);
                }
            }
        }
    
}

