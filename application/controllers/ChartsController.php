<?php

class ChartsController extends Zend_Controller_Action
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

    public function plotAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currMode = '';
        $result = $this->buildStepArray($currId, $currMode);
        $this->view->result = $result;
//        var_dump($result);
//        die();
        $jsData = '';
        $this->view->jsData = $jsData;

        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.barRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/jqplot.pieRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.categoryAxisRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.pointLabels.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/jquery.jqplot.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jqplot/jquery.jqplot.css');
        }

    private function buildStepArray($currId, $currMode)
        {
        $sql = 'SELECT prog_steps.Inizio, prog_steps.Fine, ' .
                'richieste.idRichiesta AS keyRichiesta, ' .
                'qualifiche.Descrizione AS Qualifica, ' .
                'personale.Nome AS Nome, ' .
                'personale.Cognome AS Cognome, ' .
                'forn_pers.RagSoc AS RagSoc, ' .
                'contratti.Contratto AS Contratto, ' .
                'contratti.Sigla AS Sigla, ' .
                'CONCAT(LPAD(giorni_lav.Mese, 2, "0"), "/", giorni_lav.Anno)  AS GGLavKey, ' .
                'giorni_lav.GiorniTarget AS GiorniTarget, ' .
                'giorni_lav.GiorniEff AS GiorniEff, ' .
                'giorni_lav.FerieEff AS FerieEff, ' .
                'giorni_lav.PermEff AS PermEff, ' .
                'giorni_lav.MalEff AS MalEff ' .
                'FROM prog_steps ' .
                'JOIN richieste ON richieste.idProgStep = prog_steps.idProgStep ' .
                'JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica ' .
                'LEFT JOIN personale ON personale.idPersonale = richieste.idPersonale ' .
                'LEFT JOIN contratti ON contratti.idContratto = richieste.idContratto ' .
                'LEFT JOIN forn_pers ON forn_pers.idFornPers = personale.idFornPers ' .
                'LEFT JOIN giorni_lav ON giorni_lav.idRichiesta = richieste.idRichiesta ' .
                'WHERE prog_steps.idProgStep = "' . $currId . '" ' .
                'ORDER BY richieste.idRichiesta, giorni_lav.Anno, giorni_lav.Mese';
        $allAtt = $this->db->fetchAll($sql);
        $currAtt = $allAtt[0];
        $emptyArr = array();
        $start = new DateTime($currAtt['Inizio']);
        $start->modify('first day of this month');
//        $start = (new DateTime($currAtt['Inizio']))->modify('first day of this month');
        $end = new DateTime($currAtt['Fine']);
        $end->modify('first day of next month');
//        $end = (new DateTime($currAtt['Fine']))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $currTotTarLine = array('Qualifica' => 'Tot. target',
            'keyRichiesta' => '');
        $currTotEffLine = array('Qualifica' => 'Tot. effettivo',
            'keyRichiesta' => '');
        $currProgTarLine = array('Qualifica' => 'Progr. target',
            'keyRichiesta' => '');
        $currProgEffLine = array('Qualifica' => 'Progr. effettivo',
            'keyRichiesta' => '');
        foreach ($period as $dt)
            {
            $emptyArr[$dt->format("m/Y")] = 0;
            $currProgTarLine[$dt->format("m/Y")] = 0;
            $currTotTarLine[$dt->format("m/Y")] = 0;
            $currProgEffLine[$dt->format("m/Y")] = 0;
            $currTotEffLine[$dt->format("m/Y")] = 0;
            }
        $retArr = array();
        $currIdReq = '';
        if ($currMode == 't')
            {
            $currModeField = 'GiorniTarget';
            }
        else
            {
            $currModeField = 'GiorniEff';
            }
        foreach ($allAtt as $att)
            {
            if ($currIdReq != $att['keyRichiesta'])
                {
                $currIdReq = $att['keyRichiesta'];
                $currAttLine = array('Qualifica' => $att['Qualifica'],
                    'Nome' => $att['Nome'],
                    'Cognome' => $att['Cognome'],
                    'RagSoc' => $att['RagSoc'],
                    'Contratto' => $att['Contratto'],
                    'Sigla' => $att['Sigla'],
                    'keyRichiesta' => $att['keyRichiesta'],
                    'GiorniTarget' => $emptyArr,
                    'GiorniEff' => $emptyArr,
                    'Data' => array());
                foreach ($period as $dt)
                    {
                    $currAttLine[$dt->format("m/Y")] = 0;
                    $currAttLine['Data'][$dt->format("m/Y")] = array(
                        'GiorniTarget' => 0,
                        'GiorniEff' => 0,
                        'FerieEff' => 0,
                        'PermEff' => 0,
                        'MalEff' => 0);
                    }
                if ($att[$currModeField] != '')
                    {
                    $currAttLine[$att['GGLavKey']] = number_format($att[$currModeField]);
//                    $currTotLine[$att['GGLavKey']] += $att[$currModeField];
                    $currTotTarLine[$att['GGLavKey']] += $att['GiorniTarget'];
                    $currTotEffLine[$att['GGLavKey']] += $att['GiorniEff'];
                    }
                $retArr['id-' . $currIdReq] = $currAttLine;
                }
            else if ($currIdReq != '')
                {
                $retArr['id-' . $currIdReq][$att['GGLavKey']] = number_format($att[$currModeField]);
                $currTotTarLine[$att['GGLavKey']] += $att['GiorniTarget'];
                $currTotEffLine[$att['GGLavKey']] += $att['GiorniEff'];
                }
            $retArr['id-' . $currIdReq]['Data'][$att['GGLavKey']] = array(
                'GiorniTarget' => number_format($att['GiorniTarget']),
                'GiorniEff' => number_format($att['GiorniEff']),
                'FerieEff' => number_format($att['FerieEff']),
                'PermEff' => number_format($att['PermEff']),
                'MalEff' => number_format($att['MalEff']));
            $retArr['id-' . $currIdReq]['GiorniTarget'][$att['GGLavKey']] = number_format($att['GiorniTarget']);
            $retArr['id-' . $currIdReq]['GiorniEff'][$att['GGLavKey']] = number_format($att['GiorniEff']);
            }
        $retArr['totTarget'] = $currTotTarLine;
        $retArr['totEff'] = $currTotEffLine;
        $currProgTar = 0;
        $currProgEff = 0;
        foreach ($period as $dt)
            {
            $currProgTar += $currTotTarLine[$dt->format("m/Y")];
            $currProgEff += $currTotEffLine[$dt->format("m/Y")];
            $currProgTarLine[$dt->format("m/Y")] = $currProgTar;
            $currProgEffLine[$dt->format("m/Y")] = $currProgEff;
            }
        $retArr['progressivoTarget'] = $currProgTarLine;
        $retArr['progressivoEffettivo'] = $currProgEffLine;
        return $retArr;
        }

    private function buildGanttArray($currId, $currMode)
        {
        $sql = 'SELECT prog_steps.Inizio, prog_steps.Fine, ' .
                'richieste.idRichiesta AS keyRichiesta, ' .
                'qualifiche.Descrizione AS Qualifica, ' .
                'personale.Nome AS Nome, ' .
                'personale.Cognome AS Cognome, ' .
                'forn_pers.RagSoc AS RagSoc, ' .
                'contratti.Contratto AS Contratto, ' .
                'contratti.Sigla AS Sigla, ' .
                'CONCAT(LPAD(giorni_lav.Mese, 2, "0"), "/", giorni_lav.Anno)  AS GGLavKey, ' .
                'giorni_lav.GiorniTarget AS GiorniTarget, ' .
                'giorni_lav.GiorniEff AS GiorniEff, ' .
                'giorni_lav.FerieEff AS FerieEff, ' .
                'giorni_lav.PermEff AS PermEff, ' .
                'giorni_lav.MalEff AS MalEff ' .
                'FROM prog_steps ' .
                'JOIN richieste ON richieste.idProgStep = prog_steps.idProgStep ' .
                'JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica ' .
                'LEFT JOIN personale ON personale.idPersonale = richieste.idPersonale ' .
                'LEFT JOIN contratti ON contratti.idContratto = richieste.idContratto ' .
                'LEFT JOIN forn_pers ON forn_pers.idFornPers = personale.idFornPers ' .
                'LEFT JOIN giorni_lav ON giorni_lav.idRichiesta = richieste.idRichiesta ' .
                'WHERE prog_steps.idProgStep = "' . $currId . '" ' .
                'ORDER BY richieste.idRichiesta, giorni_lav.Anno, giorni_lav.Mese';
        $allAtt = $this->db->fetchAll($sql);
        $currAtt = $allAtt[0];
        $emptyArr = array();
        $start = new DateTime($currAtt['Inizio']);
        $start->modify('first day of this month');
//        $start = (new DateTime($currAtt['Inizio']))->modify('first day of this month');
        $end = new DateTime($currAtt['Fine']);
        $end->modify('first day of next month');
//        $end = (new DateTime($currAtt['Fine']))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $currTotTarLine = array('Qualifica' => 'Tot. target',
            'keyRichiesta' => '');
        $currTotEffLine = array('Qualifica' => 'Tot. effettivo',
            'keyRichiesta' => '');
        $currProgTarLine = array('Qualifica' => 'Progr. target',
            'keyRichiesta' => '');
        $currProgEffLine = array('Qualifica' => 'Progr. effettivo',
            'keyRichiesta' => '');
        foreach ($period as $dt)
            {
            $emptyArr[$dt->format("m/Y")] = 0;
            $currProgTarLine[$dt->format("m/Y")] = 0;
            $currTotTarLine[$dt->format("m/Y")] = 0;
            $currProgEffLine[$dt->format("m/Y")] = 0;
            $currTotEffLine[$dt->format("m/Y")] = 0;
            }
        $retArr = array();
        $currIdReq = '';
        if ($currMode == 't')
            {
            $currModeField = 'GiorniTarget';
            }
        else
            {
            $currModeField = 'GiorniEff';
            }
        foreach ($allAtt as $att)
            {
            if ($currIdReq != $att['keyRichiesta'])
                {
                $currIdReq = $att['keyRichiesta'];
                $currAttLine = array('Qualifica' => $att['Qualifica'],
                    'Nome' => $att['Nome'],
                    'Cognome' => $att['Cognome'],
                    'RagSoc' => $att['RagSoc'],
                    'Contratto' => $att['Contratto'],
                    'Sigla' => $att['Sigla'],
                    'keyRichiesta' => $att['keyRichiesta'],
                    'GiorniTarget' => $emptyArr,
                    'GiorniEff' => $emptyArr,
                    'Data' => array());
                foreach ($period as $dt)
                    {
                    $currAttLine[$dt->format("m/Y")] = 0;
                    $currAttLine['Data'][$dt->format("m/Y")] = array(
                        'GiorniTarget' => 0,
                        'GiorniEff' => 0,
                        'FerieEff' => 0,
                        'PermEff' => 0,
                        'MalEff' => 0);
                    }
                if ($att[$currModeField] != '')
                    {
                    $currAttLine[$att['GGLavKey']] = number_format($att[$currModeField]);
//                    $currTotLine[$att['GGLavKey']] += $att[$currModeField];
                    $currTotTarLine[$att['GGLavKey']] += $att['GiorniTarget'];
                    $currTotEffLine[$att['GGLavKey']] += $att['GiorniEff'];
                    }
                $retArr['id-' . $currIdReq] = $currAttLine;
                }
            else if ($currIdReq != '')
                {
                $retArr['id-' . $currIdReq][$att['GGLavKey']] = number_format($att[$currModeField]);
                $currTotTarLine[$att['GGLavKey']] += $att['GiorniTarget'];
                $currTotEffLine[$att['GGLavKey']] += $att['GiorniEff'];
                }
            $retArr['id-' . $currIdReq]['Data'][$att['GGLavKey']] = array(
                'GiorniTarget' => number_format($att['GiorniTarget']),
                'GiorniEff' => number_format($att['GiorniEff']),
                'FerieEff' => number_format($att['FerieEff']),
                'PermEff' => number_format($att['PermEff']),
                'MalEff' => number_format($att['MalEff']));
            $retArr['id-' . $currIdReq]['GiorniTarget'][$att['GGLavKey']] = number_format($att['GiorniTarget']);
            $retArr['id-' . $currIdReq]['GiorniEff'][$att['GGLavKey']] = number_format($att['GiorniEff']);
            }
        $retArr['totTarget'] = $currTotTarLine;
        $retArr['totEff'] = $currTotEffLine;
        $currProgTar = 0;
        $currProgEff = 0;
        foreach ($period as $dt)
            {
            $currProgTar += $currTotTarLine[$dt->format("m/Y")];
            $currProgEff += $currTotEffLine[$dt->format("m/Y")];
            $currProgTarLine[$dt->format("m/Y")] = $currProgTar;
            $currProgEffLine[$dt->format("m/Y")] = $currProgEff;
            }
        $retArr['progressivoTarget'] = $currProgTarLine;
        $retArr['progressivoEffettivo'] = $currProgEffLine;
        return $retArr;
        }

    public function ganttAction()
        {
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/jquery_gantt/js/jquery.min.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery_gantt/js/jquery.fn.gantt.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/jquery_gantt/test/moment.min.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery_gantt/css/style.css');
        }

    }
