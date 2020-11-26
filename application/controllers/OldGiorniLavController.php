<?php

class OldGiorniLavController extends Zend_Controller_Action
    {

    private $localData = null;

    public function init()
        {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
        }

    public function indexAction()
        {
        // action body
        }

    public function gridAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currMode = $request->getParam('mode');
        if($currMode == 't')
            {
            $currTitle = 'Target';
            }
        else
            {
            $currTitle = 'Effettivi';          
            }
        $this->findStep($currId);
        $this->setStepTitle(' - '.$currTitle);
        $result = $this->buildStepArray($currId, $currMode);
        $keys=array();
        $values=array();
        $totals=array("GiorniTarget" => 0, "GiorniEff" => 0, "FerieEff" => 0, 
            "PermEff" => 0, "MalEff" => 0 );
        $jsData="\n".'var currData = { '."\n";
        $arrId=1;
        foreach($result as $line)
            {
            if($line['keyRichiesta'] != '')
                {
                $jsData .= "\n".'"'.$arrId++.'": { 
                        "Id":"'        . $line['keyRichiesta'] . '",
                        "Nome":"'      . $line['Nome']      . '",
                        "Cognome":"'   . $line['Cognome']   . '",
                        "RagSoc":"'    . $line['RagSoc']    . '",
                        "Contratto":"' . $line['Contratto'] . '",
                        "Sigla":"'     . $line['Sigla']     . '",
                        "GGM": {
                                                ';
                foreach($line['Data'] as $keyData => $dato)
                    {
                    $jsData .= "\n".'"'.$keyData .'": { ';
                    foreach($dato as $key => $field)
                        {
                        $jsData .= '"'.$key.'": '.$field.',';
                        $totals[$key]+=$field;
                        }
                    $jsData .= ' }, '."\n";
                    }
                $jsData .= ' }, '."\n";
                $jsData .= '"Totali": { ';
                foreach($totals as $key => $field)
                    {
                    $jsData .= '"'.$key.'": '.$field.',';
                    }
                $jsData .= ' }, '."\n";
                $jsData .= ' }, '."\n";
                unset($line['Data']);
                array_push($keys, $line['keyRichiesta']);
                }
            unset($line['keyRichiesta']);
            unset($line['Nome']);
            unset($line['Cognome']);
            unset($line['RagSoc']);
            unset($line['Contratto']);
            unset($line['Sigla']);
            $values[] = $line;
            }
        $jsData .= ' };'."\n";

//        $grid = $this->makeGrid($this->localData);
        $grid = $this->MyGrid();
        $source = new Bvb_Grid_Source_Array($values, array_keys($values[0]));
        $grid->setSource($source);
//        $grid->updateColumn('KeyRichiesta', array('hidden'=>true));
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/heavy-table/javascripts/jquery.heavyTable.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/heavy-table/stylesheets/style.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/heavy-table/stylesheets/normalize.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles_heavy.css');
        $this->view->jsGrid = "";
        $this->view->jsData = $jsData;
        $this->view->title = $currTitle;          
        $this->view->mode = $currMode;
        $this->view->keys = implode(',', $keys);
        $this->view->gridLen = count($values[0]);
//        $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        $this->view->cantiere = $defaultNamespace->cantiere;
        }

    public function savegridAction()
        {
        $request = $this->getRequest();
        $currMode = $request->getParam('mode');
        $currGGLav = $request->getParam('gglav');
        $currKeys = $request->getParam('keys');
        $currGridLen = $request->getParam('gridlen');
        $keys = explode(',', $currKeys);
        $GGLavArr = array_chunk(explode(',', $currGGLav), $currGridLen);
        $newGGLav = array();
        for($x=1; $x <= count($keys); $x++)
            {
            for($y = 1; $y < $currGridLen; $y++)
                {
                $newKey=$keys[$x-1].'/'.$GGLavArr[0][$y];
                $newGGLav[$newKey]=$GGLavArr[$x][$y];
                }
            }
        if($currMode == 't')
            {
            $currModeField = 'GiorniTarget';
            }
        else
            {
            $currModeField = 'GiorniEff';            
            }
        
        $sql = 'SELECT CONCAT(idRichiesta, "/", LPAD(Mese, 2, "0"), "/", Anno) AS oldKey, 
                   idGiorniLav, idRichiesta, Anno, Mese, GiorniTarget, GiorniEff
                FROM giorni_lav
                WHERE idRichiesta IN(' . $currKeys . ')';
        $oldGGLav = $this->db->fetchAll($sql);
        
        // Update old records
        $updSql='';
        foreach ($oldGGLav as $oldRecord)
            {
            if($oldRecord[$currModeField] != $newGGLav[$oldRecord['oldKey']])
                {
                $updSql .= 'UPDATE giorni_lav SET '.$currModeField.'="'.
                        $newGGLav[$oldRecord['oldKey']].'" '.
                        'WHERE idGiorniLav="'. $oldRecord['idGiorniLav'] .'";';
                }
            $newGGLav[$oldRecord['oldKey']]=0;
            }
        if($updSql != '')
            {
            $this->db->query($updSql);
            }
        
        // Insert new records
        $insSql = '';
        foreach ($newGGLav as $newKey=>$newValue)
            {
            if($newValue != 0 && !array_key_exists($newKey, $oldGGLav))
                {
                $newKeyArr= explode('/', $newKey);
                $insSql .= 'INSERT INTO giorni_lav  
                            (idRichiesta, Mese, Anno, '.$currModeField.') 
                            VALUES ("'.
                $newKeyArr[0].'","'.
                $newKeyArr[1].'","'.
                $newKeyArr[2].'","'.
                $newValue.'");';
                }
            }
        var_dump($updSql, $insSql);    
        if($insSql != '')
            {
            $this->db->query($insSql);
            }
            
        // Cleanup
        $sql = 'DELETE FROM giorni_lav WHERE idRichiesta IN(' . $currKeys . 
                ') AND GiorniTarget = 0 AND GiorniEff = 0';
        $this->db->query($sql);
        $this->_redirect('/progetti/list');
        }

    public function effettiviAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findStep($currId);
        $this->setStepTitle();
        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'richieste');
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

    private function MyGrid($title = '')
        {
        $config = new Zend_Config_Ini(APPLICATION_PATH .
                '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('Table', $config);
        $grid->setExport(array('pdf', 'word', 'excel', 'print'));
        $grid->addTemplateDir('My/Template/Table', 'My_Template_Table', 'table');
        $grid->addFormatterDir('My/Grid/Formatter', 'My_Grid_Formatter');
        $grid->setImagesUrl(APPLICATION_PATH . '../public/images/');
        $grid->setEscapeOutput(false);
        $grid->setUseKeyEventsOnFilters(true);
        $grid->setAlwaysShowOrderArrows(false);
        $grid->setNoOrder(true);
        $grid->setNoFilters(1);
//        $grid->setNoOrder(0);

        $grid->setParam('deploy.pdf.title', $title)
                ->setParam('deploy.pdf.subtitle', '')
                ->setParam('deploy.print.title', $title);
        return $grid;
        }

    private function buildStepArray($currId, $currMode)
        {
        $sql = 'SELECT prog_steps.Inizio, prog_steps.Fine, ' .
                'richieste.idRichiesta AS keyRichiesta, ' .
                'qualifiche.Descrizione AS Qualifica, ' .
                'CONCAT(personale.Nome, " ", personale.Cognome) AS NomeCognome, ' .
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
                'JOIN jobs ON jobs.idRichiesta = richieste.idRichiesta ' .
                'JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica ' .
                'LEFT JOIN personale ON personale.idPersonale = jobs.idPersonale '. 
                'LEFT JOIN contratti ON contratti.idContratto = richieste.idContratto '. 
                'LEFT JOIN forn_pers ON forn_pers.idFornPers = personale.idFornPers '. 
                'LEFT JOIN giorni_lav ON giorni_lav.idRichiesta = richieste.idRichiesta '. 
                'WHERE prog_steps.idProgStep = "' . $currId . '" '.
                'ORDER BY richieste.idRichiesta, giorni_lav.Anno, giorni_lav.Mese';
        $allAtt = $this->db->fetchAll($sql);
        $currAtt = $allAtt[0];
        $start = new DateTime($currAtt['Inizio']);
        $start->modify('first day of this month');
//        $start = (new DateTime($currAtt['Inizio']))->modify('first day of this month');
        $end = new DateTime($currAtt['Fine']);
        $end->modify('first day of next month');
//        $end = (new DateTime($currAtt['Fine']))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $currTotTarLine = array('Qualifica' => 'Totale',
                                'NomeCognome' => '',
                                     'keyRichiesta' => '');
        $currTotEffLine = array('Qualifica' => 'Tot. effettivo',
                                'NomeCognome' => '',
                                     'keyRichiesta' => '');
        $currProgTarLine = array('Qualifica' => 'Progressivo',
                                'NomeCognome' => '',
                                     'keyRichiesta' => '');
        $currProgEffLine = array('Qualifica' => 'Progr. effettivo',
                                'NomeCognome' => '',
                                     'keyRichiesta' => '');
        foreach ($period as $dt)
            {
            $currProgTarLine[$dt->format("m/Y")] = 0;
            $currTotTarLine[$dt->format("m/Y")] = 0;
            $currProgEffLine[$dt->format("m/Y")] = 0;
            $currTotEffLine[$dt->format("m/Y")] = 0;
            }
        $retArr = array();
        $currIdReq='';
        if($currMode == 't')
            {
            $currModeField = 'GiorniTarget';
            }
        else
            {
            $currModeField = 'GiorniEff';            
            }
        foreach ($allAtt as $att)
            {
            if($currIdReq != $att['keyRichiesta'])
                {
                $currIdReq = $att['keyRichiesta'];
                $currAttLine = array('Qualifica' => $att['Qualifica'],
                                    'Supervisor'  => $att['NomeCognome'],
                                    'Nome'  => $att['Nome'],
                                    'Cognome'  => $att['Cognome'],
                                    'RagSoc'    => $att['RagSoc'],
                                    'Contratto'  => $att['Contratto'],
                                    'Sigla'      => $att['Sigla'],
                                    'keyRichiesta' => $att['keyRichiesta'],
                                    'Data' => array());
                foreach ($period as $dt)
                    {
                    $currAttLine[$dt->format("m/Y")] = 0;
                    $currAttLine['Data'][$dt->format("m/Y")] = array(
                                                'GiorniTarget' => 0,
                                                'GiorniEff' => 0,
                                                'FerieEff' => 0,
                                                'PermEff' => 0,
                                                'MalEff' => 0  );
                    }
                if($att[$currModeField] != '')
                    {
                    $currAttLine[$att['GGLavKey']] = number_format($att[$currModeField]);
//                    $currTotLine[$att['GGLavKey']] += $att[$currModeField];
                    $currTotTarLine[$att['GGLavKey']] += $att['GiorniTarget'];
                    $currTotEffLine[$att['GGLavKey']] += $att['GiorniEff'];
                    }
                $retArr['id-'.$currIdReq] = $currAttLine;
                }
            else if($currIdReq != '')
                {
                $retArr['id-'.$currIdReq][$att['GGLavKey']] = number_format($att[$currModeField]);                
                $currTotTarLine[$att['GGLavKey']] += $att['GiorniTarget'];
                $currTotEffLine[$att['GGLavKey']] += $att['GiorniEff'];
                }
            $retArr['id-'.$currIdReq]['Data'][$att['GGLavKey']] = array(
                'GiorniTarget' => number_format($att['GiorniTarget']),
                'GiorniEff' => number_format($att['GiorniEff']),
                'FerieEff' => number_format($att['FerieEff']),
                'PermEff' => number_format($att['PermEff']),
                'MalEff' => number_format($att['MalEff']));
            }            
        $retArr['totTarget'] = $currTotTarLine;
//        $retArr['totEff'] = $currTotEffLine;
        $currProgTar=0;
        $currProgEff=0;
        foreach ($period as $dt)
            {
            $currProgTar += $currTotTarLine[$dt->format("m/Y")];
            $currProgEff += $currTotEffLine[$dt->format("m/Y")];
            $currProgTarLine[$dt->format("m/Y")] = $currProgTar;
            $currProgEffLine[$dt->format("m/Y")] = $currProgEff;
            }
        $retArr['progressivoTarget'] = $currProgTarLine;
//        $retArr['progressivoEffettivo'] = $currProgEffLine;
        return $retArr;
        }

        
        
//var myObj = { 
//    fred: { apples: 2, oranges: 4, bananas: 7, melons: 0 }, 
//    mary: { apples: 0, oranges: 10, bananas: 0, melons: 0 }, 
//    sarah: { apples: 0, oranges: 0, bananas: 0, melons: 5 } 
//}        
        
        
    private function findProject($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = 'SELECT * FROM progetti ' .
                    'WHERE idProgetto = "' . $currId . '"';
            $currProject = $this->db->fetchRow($sql);
            $defaultNamespace->currProject = $currProject;
            }
        }

    private function setProjTitle()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = "Progetto " . $defaultNamespace->currProject['Nome'];
//        $config->cantiere->get('nome') . " " . $config->cantiere->get('sottotitolo') .                
        }

    private function findStep($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
//        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = 'SELECT * FROM prog_steps ' .
                    'WHERE idProgStep = "' . $currId . '"';
            $currStep = $this->db->fetchRow($sql);
            $this->findProject($currStep['idProgetto']);
            $defaultNamespace->currStep = $currStep;
            }
        }

    private function setStepTitle($title)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = "Commessa " . $defaultNamespace->currProject['Nome'] .
                " : Attività " . $defaultNamespace->currStep['Step'].$title;
//        $defaultNamespace->cantiere = $config->cantiere->get('nome') .
//                " " . $config->cantiere->get('sottotitolo') .
//                " - Commessa " . $defaultNamespace->currProject['Nome'] .
//                " : Attività " . $defaultNamespace->currStep['Step'].$title;
        }

    }
