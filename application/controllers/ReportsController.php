<?php

class ReportsController extends Zend_Controller_Action
    {

    public function init()
        {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
        /* Initialize action controller here */
        }

    public function indexAction()
        {
        // action body
        }

    public function totReportAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $defaultNamespace->cant2 = $translate->_("Totali");
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'select_tot');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/reports/tot-report/id/' . $currId);
        $listJobs = '';
        $listQual = '';
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();

            $needDays = (array_key_exists('Days', $formData) && $formData['Days']);
            $listJobs = isset($formData['idMultiJobs']) ? implode(',', $formData['idMultiJobs']) : '';
            $listQual = isset($formData['idMultiQual']) ? implode(',', $formData['idMultiQual']) : '';
            $listForn = isset($formData['idMultiForn']) ? implode(',', $formData['idMultiForn']) : '';
            $listSearch = isset($formData['idMultiSearch']) ? implode(',', $formData['idMultiSearch']) : '';
            $listPW = isset($formData['idMultiForn']) ? implode(',', $formData['idMultiForn']) : '';
            if(array_key_exists('startDate', $formData) && $formData['startDate'] != '')
                {
                $startDate = (new DateTime($formData['startDate']))->modify('first day of');
                $start = $startDate->format('Y-m'); 
                }
            else    
                {
                $startDate = false;
                $start = '-'; 
                }
            if(array_key_exists('endDate', $formData) && $formData['endDate'] != '')
                {
                $endDate = (new DateTime($formData['endDate']))->modify('last day of');
                $end = $endDate->format('Y-m'); 
                }
            else    
                {
                $endDate = false;
                $end = '-'; 
                }

            $sql = "SELECT \n" .
                    "  giorni_lav.Anno, \n" .
                    "  giorni_lav.Mese, \n" .
                    "  CONCAT(LPAD(giorni_lav.Mese, 2, \"0\"), \"/\", giorni_lav.Anno) AS GGLavKey, \n" .
                    "  CONCAT(giorni_lav.Anno, \"-\", LPAD(giorni_lav.Mese,2,\"0\")) AS AnnoMese, \n" .
                    "  qualifiche.Descrizione AS Qualifica, \n" .
                    "  pers.Cognome, \n" .
                    "  pers.Nome, \n" .
                    "  COALESCE(fp1.Tipo, fp2.Tipo) AS PWTipo, \n" .
                    "  fp1.RagSoc AS RagSoc, \n" .
                    "  fp1.Nome AS FornPers, \n" .
                    "  fp1.Tipo AS FPTipo, \n" .
                    "  fp2.RagSoc AS CurrRagSoc, \n" .
                    "  fp2.Nome AS CurrFornPers, \n" .
                    "  fp2.Tipo AS CurrFPTipo, \n" .
                    "  IF(pers.idPersonale IS NULL OR pers.Cognome IN (\"DELETED\", \"DEFINED\"), 0, 1) AS Assigned, \n" .
                    "  IF(qualifiche.idQualifica IS NOT NULL, 1, 0) AS TotQualifiche, \n" .
                    "  (COALESCE(dett_giorni_lav.TotGGLav, 0)+ \n" .
                    "    CASE WHEN giorni_lav.GiorniEff <> 0 \n" .
                    "       THEN giorni_lav.GiorniEff \n" .
                    "       ELSE giorni_lav.GiorniTarget \n" .
                    "    END ) AS TotDays, \n" .
                    "  IF( \n" .
                    "    contratti3.idContratto IS NULL, \n" .
                    "    contratti2.Contratto, \n" .
                    "    contratti3.Contratto \n" .
                    "  ) AS Contratto, \n" .
                    "  IF( \n" .
                    "    contratti3.idContratto IS NULL, \n" .
                    "    contratti2.idContratto, \n" .
                    "    contratti3.idContratto \n" .
                    "  ) AS idContratto, \n" .
                    "  IF( \n" .
                    "    contratti3.idContratto IS NULL, \n" .
                    "    contratti2.Sigla, \n" .
                    "    contratti3.Sigla \n" .
                    "  ) AS Sigla, \n" .
                    "  IF( \n" .
                    "    contratti3.idContratto IS NULL, \n" .
                    "    contratti2.Sigla = \"PW PARTNER\", \n" .
                    "    contratti3.Sigla = \"PW PARTNER\" \n" .
                    "  ) AS isPwPartner, \n" .
                    "  IF( \n" .
                    "    contratti3.idContratto IS NULL, \n" .
                    "    contratti2.Sigla = \"Client\", \n" .
                    "    contratti3.Sigla = \"Client\" \n" .
                    "  ) AS isClient, \n" .
                    "  ( \n" .
                    "    contratti3.idContratto IS NULL OR contratti3.idContratto = contratti2.idContratto \n" .
                    "  ) AS MainContrOwned, \n" .
                    "  contratti3.idContratto, \n" .
                    "  (contratti3.Sigla != \"Client\" && contratti3.Sigla != \"PW_PARTNER\") AS isPwoe, \n" .
                    "  aree.Vendor, \n" .
                    "  aree.NotInvoiced \n" .
                    "FROM \n" .
                    "  giorni_lav \n" .
                    "JOIN \n" .
                    "  richieste ON richieste.idRichiesta = giorni_lav.idRichiesta \n" .
                    "JOIN \n" .
                    "  qualifiche ON qualifiche.idQualifica = richieste.idQualifica \n" .
                    "JOIN \n" .
                    "  prog_steps ON prog_steps.idProgStep = richieste.idProgstep \n" .
                    "JOIN \n" .
                    "  progetti ON progetti.idProgetto = prog_steps.idProgetto \n" .
                    "JOIN \n" .
                    "  aree ON aree.idArea = prog_steps.idArea \n" .
                    "LEFT JOIN \n" .
                    "  jobs ON jobs.idRichiesta = richieste.idRichiesta \n" .
                    "LEFT JOIN \n" .
                    "  personale AS pers ON pers.idPersonale = jobs.idPersonale \n" .
                    "LEFT JOIN \n" .
                    "  pers_forn_pers AS pfp1 ON pfp1.idPersonale = jobs.idPersonale AND pfp1.idProgetto = progetti.idProgetto AND DATE( \n" .
                    "    CONCAT(giorni_lav.Anno, \n" .
                    "    \"-\", \n" .
                    "    giorni_lav.Mese, \n" .
                    "    \"-15\") \n" .
                    "  ) BETWEEN pfp1.Dal AND pfp1.Al \n" .
                    "LEFT JOIN \n" .
                    "  forn_pers AS fp1 ON fp1.idFornpers = pfp1.idFornPers \n" .
                    "LEFT JOIN \n" .
                    "  forn_pers AS fp2 ON fp2.idFornpers = pers.idFornPers \n" .
                    "LEFT JOIN \n" .
                    "  contratti AS contratti1 ON contratti1.idContratto = richieste.idContratto \n" .
                    "LEFT JOIN \n" .
                    "  contratti AS contratti2 ON contratti2.idContratto = progetti.PWMainContractor \n" .
                    "LEFT JOIN \n" .
                    "  contratti AS contratti3 ON contratti3.idContratto = aree.idContratto \n" .
                    "LEFT JOIN \n" .
                    "  dett_giorni_lav ON \n" .
                    "  dett_giorni_lav.idRichiesta = richieste.idRichiesta AND \n" .
                    "  dett_giorni_lav.Anno = giorni_lav.Anno AND \n" .
                    "  dett_giorni_lav.Mese = giorni_lav.Mese \n";

            $where = 'WHERE ( giorni_lav.GiorniTarget <> 0 OR ' .
                    '   giorni_lav.GiorniEff <> 0 OR ' .
                    '   dett_giorni_lav.TotGGNR <> 0 ) ';
            if ($start != '-')
                {
                $where .= ' AND CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) >= "' . $start . '" ';
                }
            if ($end != '-')
                {
                $where .= ' AND CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) <= "' . $end . '" ';
                }
            if ($listJobs != '')
                {
                $where .= ' AND progetti.idProgetto IN (' . $listJobs . ') ';
                }
            if ($listQual != '')
                {
                $where .= ' AND qualifiche.idQualifica IN (' . $listQual . ') ';
                }

            $sql .= $where .
                    ' ORDER BY ' .
                    '  Qualifica, ' .
                    '  giorni_lav.Anno, ' .
                    '  giorni_lav.Mese';
            
            $tot = $this->db->fetchAll($sql);
            $minYear = 9999;
            $maxYear = 0;
            foreach ($tot as $line)
                {
                if($line['Anno'] > $maxYear)
                    {
                    $maxYear = $line['Anno'];
                    }
                if($line['Anno'] < $minYear)
                    {
                    $minYear = $line['Anno'];
                    }
                }

            if ($start != '-')
                {
                $startDate = new DateTime($start . '-01');
                }
            else
                {
                $startDate = new DateTime($minYear . '-01-01');
                }
            if ($end != '-')
                {
                $endDate = new DateTime($end . '-01');
                }
            else
                {
                $endDate = new DateTime($maxYear . '-12-31');
                }
            $interval = new DateInterval('P1M');
            $endDate->add($interval);
            $period = new DatePeriod($startDate, $interval, $endDate);

//            $listPW = isset($formData['idMultiSearch']) ? implode(',', $formData['idMultiSearch']) : '';
            
//            $selectedPW = array();
//            $listPW = '';
//            if (isset($formData['idMultiSearch']))
//                {
//                foreach($formData['idMultiSearch'] as $pw)
//                    {
//                    if(is_numeric($pw))
//                        {
//                        $selectedPW[] = $pw;
//                        }
//                    }
//                $listPW = implode(',', $selectedPW);
//                }    
            $monthArr = array();
            $monthTot = array();
            $diffTot = array('pwoe' => 0, 'vend' => 0,
                'pwPartner' => 0, 'cons' => 0, 'MCpw' => 0, 'MCvend' => 0,
                'MCnotAss' => 0, 'MCcons' => 0);
            $sqlContr = 'SELECT FALSE AS Present, idContratto, ' .
                    '       Sigla, Contratto, ' .
                    '       CONCAT(Sigla, " ", Contratto) AS Contr ' .
                    'FROM contratti WHERE Sigla != "Client"';
            if($listPW != '')
                {
                $sqlContr .= ' AND idContratto IN (' . $listPW . ')';
                }
            $pwContr = $this->db->fetchAll($sqlContr);
            foreach ($pwContr as $pwLine)
                {
                $diffTot[$pwLine['idContratto']] = 0;
                $diffTot['vend_'.$pwLine['idContratto']] = 0;
                $diffTot['cons_'.$pwLine['idContratto']] = 0;
                }
            $diffTotObject = new ArrayObject($diffTot);
            foreach ($period as $dt)
                {
                $monthArr[$dt->format('Y-m')] = '';
                $monthTot[$dt->format('Y-m')] = $diffTotObject->getArrayCopy();
                }

            $totMode = '';
            $arrResult = array();
            foreach ($tot as $line)
                {
                $key = array_search($line['idContratto'], array_column($pwContr, 'idContratto'));
                if($key !== false)
                    {
                    $pwContr['Presente']=true;
                    }
                if ($line['isClient'] == 0)
                    {
                    if (!array_key_exists($line['Qualifica'], $arrResult))
                        {
                        $arrResult[$line['Qualifica']] = $monthArr;
                        }
                    if ($line['MainContrOwned'] == '1')
                        {
                        if ($line['Vendor'] == '1')
                            {
                            $totMode = 'MCvend';
                            }
                        else if ($line['Assigned'] == '0')
                            {
                            $totMode = 'MCnotAss';
                            }
                        else if ($line['PWTipo'] == 'P' || $line['PWTipo'] == 'S')
                            {
                            $totMode = 'MCpw';
                            }
                        else
                            {
                            $totMode = 'MCcons';
                            }
                        }
                    else
                        {
                        if ($line['Vendor'] == '1')
                            {
                            $totMode = 'vend';
                            }
                        else if ($line['isPwPartner'] == '1')
                            {
                            $totMode = 'pwPartner';
                            }
                        else if ($line['PWTipo'] == 'P' || $line['PWTipo'] == 'S' || $line['isPwoe'] > 0)
                            {
                            $totPwoe = array_key_exists('idMultiSearch', $formData) &&
                                    count($formData['idMultiSearch']) > 0 &&
                                    array_search($line['idContratto'], $formData['idMultiSearch']) !== FALSE;
                            $totMode = 'pwoe';
                            }
                        else
                            {
                            $totMode = 'cons';
                            }
                        }

                    if ($listPW == '' OR (array_key_exists('idMultiSearch', $formData) && 
                            array_search($totMode, $formData['idMultiSearch']) !== FALSE))
                        {
                        if ($needDays)
                            {
                            $numDays = is_numeric($line['TotDays']) ? $line['TotDays'] : 0;
                            $arrResult[$line['Qualifica']][$line['AnnoMese']] += $numDays;
                            $monthTot[$line['AnnoMese']][$line['idContratto']] += $numDays;
                            if ($totMode != 'pwoe' || $totPwoe)
                                {
                                $monthTot[$line['AnnoMese']][$totMode] += $numDays;
                                }
                            }
                        else
                            {
                            if(!is_int($arrResult[$line['Qualifica']][$line['AnnoMese']]))
                                {
                                $arrResult[$line['Qualifica']][$line['AnnoMese']] = 0;                                
                                }
                            if(array_key_exists($line['idContratto'], $monthTot[$line['AnnoMese']]) && 
                                    !is_int($monthTot[$line['AnnoMese']][$line['idContratto']]))
                                {
                                $monthTot[$line['AnnoMese']][$line['idContratto']] = 0;                                
                                }
                            $arrResult[$line['Qualifica']][$line['AnnoMese']] += $this->coalnum($line['TotQualifiche']);
                            if(array_key_exists($line['idContratto'], $monthTot[$line['AnnoMese']]))
                                {
                                $monthTot[$line['AnnoMese']][$line['idContratto']] += $this->coalnum($line['TotQualifiche']);
                                }
                            if ($totMode != 'pwoe' || $totPwoe)
                                {
                                $monthTot[$line['AnnoMese']][$totMode] += $line['TotQualifiche'];
                                }
                            }
                        }
                    }
                }
            $form->populate($formData);
            //echo $this->buildTotGrid($arrResult, $monthTot);
            $this->view->totGrid = $this->buildTotGrid($arrResult, $monthTot, $pwContr);
            }
        else
            {
            $defaults = array();
            $defaults['idMultiJobs'] = explode(',',$listJobs);
            $defaults['idMultiJobs'] = explode(',',$listQual);
            $defaults['AnnoIn'] = $defaults['AnnoFi'] = date('Y');
            $defaults['MeseIn'] = '01';
            $defaults['MeseFi'] = '12';
            $form->populate($defaults);
            $this->view->totGrid = '';
            }
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
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
                '/jquery-ui-m2/jquery.multiselect.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/assets/prettify.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.filter.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/personale_riass.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/giorni_lav_grid.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        // action body
        }

    public function dettReportAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'select_detail');
        $this->setTitle('Man/days Detail');
        $currForm->setCurrAction($defaultNamespace->baseUrl . 
                '/reports/dett-report');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $listJobs = isset($formData['idMultiJobs']) ? 
                    implode(',', $formData['idMultiJobs']) : '';
//            $this->setDefaults($formData);
            $titles = array();
            $this->view->formData = $formData;
            $where = "WHERE 1 ";
            if(array_key_exists('startDatealternate', $formData) && $formData['startDatealternate'] != '')
                {
                $startDateArr = explode('/', $formData['startDatealternate']);
                $start = $startDateArr[2].'-'.$startDateArr[1];
                $titles[] = 'From '.$startDateArr[1].'-'.$startDateArr[2];
                $startDate = true;
                }
            else    
                {
                $startDate = false;
                $start = '-'; 
                }
            if(array_key_exists('endDatealternate', $formData) && $formData['endDatealternate'] != '')
                {
                $endDateArr = explode('/', $formData['endDatealternate']);
                $end = $endDateArr[2].'-'.$endDateArr[1];
                $titles[] = 'To '.$endDateArr[1].'-'.$endDateArr[2];
                $endDate = true;
                }
            else    
                {
                $endDate = false;
                $end = '-'; 
                }

            if ($start != '-')
                {
                $where .= ' AND CONCAT(dett_giorni_lav.Anno, "-", LPAD(dett_giorni_lav.Mese,2,"0")) >= "' . $start . '" ';
                }
            if ($end != '-')
                {
                $where .= ' AND CONCAT(dett_giorni_lav.Anno, "-", LPAD(dett_giorni_lav.Mese,2,"0")) <= "' . $end . '" ';
                }
            if($listJobs != '')
                {
                $where .=  ' AND progetti.idProgetto IN ('.$listJobs.')';
                $titles[] = 'Jobs: '.$this->findJobs($listJobs);
                }    

            $sql1 = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
            $allTip = $this->db->fetchAll($sql1);
            $tipi=array("" => "Empty Value");
//            $tipi=array();
            foreach ($allTip as $currTipo)
                {
                $tipi[$currTipo['SiglaOpzione']] = $currTipo['Opzione'];            
                }
            $sql2 = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
            $allFatt = $this->db->fetchAll($sql2);
//            $fatt=array("" => 0);
            $fatt=array();
            foreach ($allFatt as $currFatt)
                {
                $fatt[$currFatt['SiglaOpzione']] = $currFatt['Opzione'];
                }
            $totGen = array();
            foreach ($fatt as $currFatt => $descFatt) 
                {
                foreach ($tipi as $currTipo => $descTipo) 
                    {
                    $totGen[$currFatt][$currTipo] = 0;
                    }
                }
            $baseTot = $totGen;
            $sql3 = "SELECT dett_giorni_lav.*, ".
                    "CONCAT(LPAD(dett_giorni_lav.Mese, 2, '0'), '-', dett_giorni_lav.Anno) AS MeseAnno ".
                "FROM dett_giorni_lav ".
                "JOIN richieste ON ".
                    "richieste.idRichiesta = dett_giorni_lav.idRichiesta ".
                "JOIN prog_steps ON prog_steps.idProgStep = richieste.idProgStep ".
                "JOIN progetti ON progetti.idProgetto = prog_steps.idProgetto ".
                $where." ORDER BY dett_giorni_lav.Anno, dett_giorni_lav.Mese";
//            die( $sql3);
            $allDett = $this->db->fetchAll($sql3);
            $tz = new DateTimeZone('Europe/Rome');
            if($allDett[0]['Anno'] == '' || $allDett[0]['Mese'] == '')
                {
                $dataMin = new DateTime();
                $dataMin->modify('first day of this month');
                $dataMax = new DateTime();
                $dataMax->modify('last day of this month');
                }
            else
                {                    
                $currDate = $allDett[0]['Anno'].'-'.$allDett[0]['Mese'].'-01';
                $dataMin = new DateTime($currDate, $tz);
                $dataMax = new DateTime($currDate, $tz);
                $dataMax->modify('last day of this month');
                }
            $allData = array();
            foreach ($allDett as $currDett) 
                {
                $dateKey = $currDett['MeseAnno'];
//                $dateKey = $currDett['Anno'].'-'.$currDett['Mese'];
                if(!array_key_exists($dateKey, $allData))
                    {
                    $allData[$dateKey] = new stdClass;
                    //@$allData[$dateKey]->singleData = array();
                    @$allData[$dateKey]->totali = $baseTot;
                    }
                //$allData[$dateKey]->singleData[] = $currDett;
                $currDate = $currDett['Anno'].'-'.$currDett['Mese'].'-01';
                $newData = new DateTime($currDate, $tz);
                if($newData < $dataMin)
                    {
                    $dataMin = clone $newData;
                    }
                $newData->modify('last day of this month');
                if($newData > $dataMax)
                    {
                    $dataMax = clone $newData;
                    }
                for($i=1; $i<=31; $i++)
                    {
                    $currIndex = sprintf("%02d", $i);
                    $fattIndex = $currDett['FattGG_'.$currIndex];
                    $tipoIndex = $currDett['TipoGG_'.$currIndex];
                    if($fattIndex != '')
                        {
                        $totGen[$fattIndex][$tipoIndex]++;
                        $allData[$dateKey]->totali[$fattIndex][$tipoIndex]++;
                        }
                    }
                }
            $this->view->titles = $titles;    
            $this->view->tipi = $tipi;    
            $this->view->fatt = $fatt;    
            $this->view->totali = $totGen;    
            $this->view->allData = $allData;    
            $this->view->formTitle = 'Man/Days Detail';
            $this->view->minYYMM = $dataMin;
            $this->view->maxYYMM = $dataMax;
            $this->view->headScript()->prependFile($this->view->baseUrl() .
                    '/js/svg.min.js');
            $this->view->headScript()->prependFile($this->view->baseUrl() .
                    '/bower_components/printThis/printThis.js');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/giorni_lav_grid.css');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/personale_riass.css');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/jobs_plan.css');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/styles.css');
            }
        else
            {
//            $defaults = $this->getDefaults($currMode);
//            $form->populate($defaults);
            $this->view->tipi = '';    
            $this->view->fatt = '';    
            $this->view->totali = '';    
            $this->view->javascript = $currForm->getJavascript();
            $this->view->form = $form;
            $this->view->formTitle = 'Man/Days Detail';
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
                    '/styles/personale_riass.css');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/giorni_lav_grid.css');
            }
        }
        
        
    private function coalNum($value)
        {
        if(is_numeric($value))
            {
            return $value;
            }
        else
            {
            return 0;
            }
        }


    private function buildTotGrid($arrResult, $totals, $pwContr)
        {
        $outTable = '<table id="totGrid" border="1" style="background-color: #ffffff">';
        $outTable .= '<caption>Totals</caption>';
        $outTable .= '<thead><tr>';
        $outTable .= '<th>Qualification</th>';
        $firstQual = reset($arrResult);
        foreach ($firstQual as $key => $value)
            {
            $outTable .= '<th>' . $key . '</th>';
            }
        $outTable .= '<th>Tot.</th>';
        $outTable .= '<th>Max.</th>';
        $outTable .= '</tr></thead>';
        $outTable .= '<tbody>';
        $totContrPW = 0;
        $totContrCons = 0;
        $totContrNotAss = 0;
        $totContrVend = 0;
        $totPW = 0;
        $totNotAss = 0;
        $totCons = 0;
        $totVend = 0;
        $totGen = 0;
        $maxGen = 0;
        $numRow = 0;
        foreach ($arrResult as $qual => $value)
            {
            $numRow++;
            if ($numRow % 2 == 0)
                {
                $oddClass = ' class="td_alt2"';
                $totClass = ' class="td_tot2"';
                }
            else
                {
                $oddClass = '';
                $totClass = ' class="td_tot"';
                }
            $outTable .= '<tr><td' . $oddClass . '>' . $qual . '</td>';
            $totMonth = 0;
            $maxMonth = 0;
            foreach ($value as $monthValue)
                {
                $outTable .= '<td' . $oddClass . '>' . $monthValue . '</td>';                
                if(is_numeric($monthValue))
                    {
                    $totMonth += $monthValue;
                    }
                if ($maxMonth < $monthValue)
                    {
                    $maxMonth = $monthValue;
                    }
                }
            $outTable .= '<td' . $totClass . '>' . $totMonth . '</td>';
            $outTable .= '<td' . $totClass . '>' . $maxMonth . '</td>';
            $totGen += $totMonth;
            $outTable .= '</tr>';
            }
        $outTable .= '</tbody>';

        $outTable .= '<tfoot>';
        
//        foreach($pwContr as $contr)
//            {
//            $totClass = ($numRow++ % 2 == 0) ? ' class="td_tot"' : ' class="td_tot2"';
//            $outTable .= $this->lineTot($totals, $contr['Sigla'], $contr['idContratto'], $totClass);
//            $totClass = ($numRow++ % 2 == 0) ? ' class="td_tot"' : ' class="td_tot2"';
//            $outTable .= $this->lineTot($totals, 'Cons. '.$contr['Sigla'], 'cons_'.$contr['idContratto'], $totClass);
//            $totClass = ($numRow++ % 2 == 0) ? ' class="td_tot"' : ' class="td_tot2"';
//            $outTable .= $this->lineTot($totals, 'Vend. '.$contr['Sigla'], 'vend_'.$contr['idContratto'], $totClass);            
//            }            

        $totClass = ($numRow++ % 2 == 0) ? ' class="td_tot"' : ' class="td_tot2"';
        $outTable .= $this->lineTot($totals, 'Vendor', 'MCvend', $totClass);
        $totClass = ($numRow++ % 2 == 0) ? ' class="td_tot"' : ' class="td_tot2"';
        $outTable .= $this->lineTot($totals, 'Not Assigned', 'MCnotAss', $totClass);
        $totClass = ($numRow++ % 2 == 0) ? ' class="td_tot"' : ' class="td_tot2"';
        $outTable .= $this->lineTot($totals, 'Paul Wurth', 'MCpw', $totClass);
        $totClass = ($numRow++ % 2 == 0) ? ' class="td_tot"' : ' class="td_tot2"';
        $outTable .= $this->lineTot($totals, 'Consultants', 'MCcons', $totClass);

        $totClass = ($numRow++ % 2 == 0) ? ' class="td_nctot"' : ' class="td_nctot2"';
        $outTable .= $this->lineTot($totals, 'Other Vendor', 'vend', $totClass);
        $totClass = ($numRow++ % 2 == 0) ? ' class="td_nctot"' : ' class="td_nctot2"';
        $outTable .= $this->lineTot($totals, 'Paul Wurth Other Entities', 'pwoe', $totClass);
        $totClass = ($numRow++ % 2 == 0) ? ' class="td_nctot"' : ' class="td_nctot2"';
        $outTable .= $this->lineTot($totals, 'Paul Wurth Partner', 'pwPartner', $totClass);
//        foreach($pwContr as $pw)
//            {
//            if(is_array($pw) && array_key_exists('Presente', $pw) && $pw['Presente'])
//                {
//                $totClass = ($numRow++ % 2 == 0) ? ' class="td_nctot"' : ' class="td_nctot2"';
//                $outTable .= $this->lineTot($totals, $pw['Contr'], $pw['idContratto'], $totClass);            
//                }
//            }
        $outTable .= '</tfoot></table>';
        return $outTable;
        }
        
    public function jobsPlanAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currMode = $request->getParam('mode');
        $currForm = new Custom_XmlForm();
        if($currMode == 'pers')
            {
            $currForm->initForm($this->db, 'select_plan_pers', 0, array('mode' => $currMode));
            $this->setTitle('Personnel Workload');
            $this->view->levelTitles = array('1' => "Supervisor", '2' => 'Job', '3' => 'Qualification');
            }
        else    
            {
            $currForm->initForm($this->db, 'select_plan_jobs', 0, array('mode' => $currMode));
            $this->setTitle('Jobs Workload');
            $this->view->levelTitles = array('1' => "Job", '2' => 'Phase', '3' => 'Activity');
            }
        $currForm->setCurrAction($defaultNamespace->baseUrl . 
                '/reports/jobs-plan/mode/' . $currMode);
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $this->_helper->viewRenderer('svg');
            $formData = $this->_request->getPost();
            $formData['Type'] = $currMode;
            $this->setDefaults($formData);
            $this->view->formData = $formData;
            $resSvg = $this->getSvg($formData);
            $this->view->titles = $resSvg->titles;
            $this->view->resSvg = $resSvg->svgData;
            $this->view->minYYMM = $resSvg->dataMin;
            $this->view->maxYYMM = $resSvg->dataMax;
            $this->view->headScript()->prependFile($this->view->baseUrl() .
                    '/js/svg.min.js');
            $this->view->headScript()->prependFile($this->view->baseUrl() .
                    '/bower_components/printThis/printThis.js');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/giorni_lav_grid.css');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/personale_riass.css');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/jobs_plan.css');
            }
        else
            {
            $defaults = $this->getDefaults($currMode);
            $form->populate($defaults);
            $this->view->totGrid = '';
            $this->view->javascript = $currForm->getJavascript();
            $this->view->form = $form;
            if($currMode == 'pers')
                {
                $this->view->formTitle = 'Personnel Workload';
                }
            else    
                {
                $this->view->formTitle = 'Jobs Workload';
                }
                
                
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
                    '/styles/personale_riass.css');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                    '/styles/giorni_lav_grid.css');
                
                                
//            $this->view->headScript()->prependFile($this->view->baseUrl() .
//                    '/js/jquery_form.js');
//            $this->view->headScript()->prependFile($this->view->baseUrl() .
//                    '/js/jquery.populate.js');
//            $this->view->headScript()->prependFile($this->view->baseUrl() .
//                    '/jquery-ui-m/src/jquery.multiselect.filter.js');
//            $this->view->headScript()->prependFile($this->view->baseUrl() .
//                    '/jquery-ui-m/assets/prettify.js');
//            $this->view->headScript()->prependFile($this->view->baseUrl() .
//                    '/jquery-ui-m/src/jquery.multiselect.js');
//            $this->view->headScript()->prependFile($this->view->baseUrl() .
//                    '/multiselect/js/plugins/localisation/jquery.localisation-min.js');
//
//            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                    '/styles/styles.css');
//            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                    '/jquery-ui-m/jquery.multiselect.css');
//            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                    '/jquery-ui-m/assets/prettify.css');
//            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                    '/jquery-ui-m/jquery.multiselect.filter.css');
//            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                    '/styles/personale_riass.css');
//            $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                    '/styles/giorni_lav_grid.css');
            }
        
        }
        
    private function setDefaults($formData)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        unset($formData['startDate']);
        unset($formData['endDate']);
        $defaultNamespace->defSelJP = $formData;
        }
        
    private function getDefaults($mode)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        if(isset($defaultNamespace->defSelJP))
            {
            $formData = $defaultNamespace->defSelJP;
            }
        else
            {
            $formData = array('Type' => $mode);
            }
        return $formData;    
        }
        

    private function getSvg($formData)
        {
        $titles = array();
        $listJobs = isset($formData['idMultiJobs']) ? implode(',', $formData['idMultiJobs']) : '';
        $listQual = isset($formData['idMultiQual']) ? implode(',', $formData['idMultiQual']) : '';
        $listForn = isset($formData['idMultiForn']) ? implode(',', $formData['idMultiForn']) : '';
        $listPers = isset($formData['idMultiPers']) ? implode(',', $formData['idMultiPers']) : '';
        $planType = isset($formData['Type']) ? $formData['Type'] : 'pers';
        $residentsOnly = isset($formData['Resident']) && $formData['Resident'] == '1';
        if(array_key_exists('startDatealternate', $formData) && $formData['startDatealternate'] != '')
            {
            $startDateArr = explode('/', $formData['startDatealternate']);
            $start = $startDateArr[2].'-'.$startDateArr[1];
            $titles[] = 'From '.$startDateArr[1].'-'.$startDateArr[2];
            $startDate = true;
            }
        else    
            {
            $startDate = false;
            $start = '-'; 
            }
        if(array_key_exists('endDatealternate', $formData) && $formData['endDatealternate'] != '')
            {
            $endDateArr = explode('/', $formData['endDatealternate']);
            $end = $endDateArr[2].'-'.$endDateArr[1];
            $titles[] = 'To '.$endDateArr[1].'-'.$endDateArr[2];
            $endDate = true;
            }
        else    
            {
            $endDate = false;
            $end = '-'; 
            }
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }
        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }
            
        $coalDetail = "COALESCE( ";
        $selFields = '';
        for($i=1; $i<=31; $i++)
            {
            if($i>1)
                {
                $coalDetail .= ',';
                }
            $coalDetail .= sprintf("dett_giorni_lav.TipoGG_%02d,\n ".
                            "dett_giorni_lav.FattGG_%02d\n ", $i, $i);
            $selFields  .= sprintf("dett_giorni_lav.OreGG_%02d,\n ".
                            "dett_giorni_lav.TipoGG_%02d,\n ".
                            "dett_giorni_lav.FattGG_%02d,\n ", $i, $i, $i);
            }
        $coalDetail .= ") <> '' ";

        $where = 'WHERE (giorni_lav.GiorniTarget <> 0 OR ' .
                '   giorni_lav.GiorniEff <> 0 OR ' .
                '   dett_giorni_lav.TotGGLav <> 0 OR ' .
                '   dett_giorni_lav.TotGGNR <> 0 OR '.$coalDetail.') ';

        if ($start != '-')
            {
            $where .= ' AND CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) >= "' . $start . '" ';
            }
        if ($end != '-')
            {
            $where .= ' AND CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) <= "' . $end . '" ';
            }
        if($listJobs != '')
            {
            $where .=  ' AND progetti.idProgetto IN ('.$listJobs.')';
            $titles[] = 'Jobs: '.$this->findJobs($listJobs);
            }    
                        
        if($listQual != '')
            {
            $where .=  ' AND qualifiche.idQualifica IN ('.$listQual.')';
            $titles[] = 'Qualifications: '. $this->findQual($listQual);
            }
            
        if($listForn != '')
            {
            $where .=  ' AND personale.idFornPers IN ('.$listForn.')';
            $titles[] = 'Suppliers: '. $this->findSupp($listForn);
            }
            
        if($listPers != '')
            {
            $where .=  ' AND personale.idPersonale IN ('.$listPers.')';
            $titles[] = 'Supervisors: '. $this->findSuper($listPers);
            }    
            
//        $where .= " AND (giorni_lav.GiorniTarget > 0 OR giorni_lav.GiorniEff > 0) ";

        if($planType == "pers")
            {
            $visSql = "SELECT \n".
                    "jobs.idPersonale, \n".
                    "giorni_lav.idGiorniLav, \n".
                    "giorni_lav.GiorniTarget, \n".
                    "giorni_lav.GiorniEff, \n".
                    "giorni_lav.Anno, \n".
                    "giorni_lav.Mese, \n".
                    "COALESCE(giorni_lav.GiorniTarget, 0) AS Planned, \n".
                    "COALESCE(giorni_lav.GiorniEff, 0) AS Actual, \n".
                    "jobs.Inizio, \n".
                    "jobs.Fine, \n".
                    "personale.Nome, \n".
                    "personale.Cognome, \n".
                    "qualifiche.idQualifica, \n".
                    "qualifiche.Descrizione AS Qualifica, \n".
                    "prog_steps.Step, \n".
                    "prog_steps.idProgStep, \n".
                    "aree.AreaShort, \n".
                    "aree.idArea, \n".
                    "dett_giorni_lav.idDettGGLav, \n".
                    $selFields." \n".
                    "(".$coalDetail.") AS hasDetail, \n".
                    "progetti.Nome AS Commessa, \n".
                    "progetti.idProgetto \n".
                    "FROM \n".
                        "richieste \n".
                    "INNER JOIN \n".
                        "giorni_lav ON giorni_lav.idRichiesta = richieste.idRichiesta \n".
                    "LEFT OUTER JOIN \n".
                        "dett_giorni_lav ON dett_giorni_lav.idRichiesta = richieste.idRichiesta AND \n".
                        "dett_giorni_lav.Anno = giorni_lav.Anno AND dett_giorni_lav.Mese = giorni_lav.Mese \n".
                    "INNER JOIN \n".
                        "jobs ON jobs.idRichiesta = richieste.idRichiesta \n".
                    "INNER JOIN \n".
                        "personale ON personale.idPersonale = jobs.idPersonale \n".
                    "INNER JOIN \n".
                        "qualifiche ON qualifiche.idQualifica = richieste.idQualifica \n".
                    "INNER JOIN \n".
                        "prog_steps ON prog_steps.idProgStep = richieste.idProgstep \n".
                    "INNER JOIN \n".
                        "aree ON aree.idArea = prog_steps.idArea \n".
                    "INNER JOIN \n".
                        "progetti ON progetti.idProgetto = prog_steps.idProgetto \n";
            if($residentsOnly)
                {
                $visSql .=            
                    "INNER JOIN \n".
                    "    pers_forn_pers AS pfp1 \n".
                    "ON \n".
                    "    pfp1.Resident = 1 AND ".    
                    "    pfp1.idPersonale = jobs.idPersonale AND ".
                    "    (pfp1.idProgetto = 0 OR ".
                    "    pfp1.idProgetto = progetti.idProgetto) AND ".
                    '    DATE(CONCAT(giorni_lav.Anno, "-", giorni_lav.Mese, "-15") '."\n".
                    "    ) BETWEEN pfp1.Dal AND pfp1.Al \n";
                }
                        
            $visSql .= $where . " ORDER BY personale.idPersonale, progetti.Nome, ".
                    "aree.AreaShort, prog_steps.idProgStep, qualifiche.idQualifica, ".
                    "giorni_lav.Anno, giorni_lav.Mese";
            $allJobs = $this->db->fetchAll($visSql);
            $svgData = $this->calcSvgPers($allJobs);
//            $visData = $this->calcNewVisPers($allJobs);
            }
        else
            {
            $visSql = "SELECT \n".
                    "jobs.idPersonale, \n".
                    "giorni_lav.idGiorniLav, \n".
                    "giorni_lav.GiorniTarget, \n".
                    "giorni_lav.GiorniEff, \n".
                    "giorni_lav.Anno, \n".
                    "giorni_lav.Mese, \n".
                    "COALESCE(giorni_lav.GiorniTarget, 0) AS Planned, \n".
                    "COALESCE(giorni_lav.GiorniEff, 0) AS Actual, \n".
                    "jobs.Inizio, \n".
                    "jobs.Fine, \n".
                    "personale.Nome, \n".
                    "personale.Cognome, \n".
                    "qualifiche.idQualifica, \n".
                    "qualifiche.Descrizione AS Qualifica, \n".
                    "prog_steps.Step, \n".
                    "prog_steps.idProgStep, \n".
                    "aree.AreaShort, \n".
                    "aree.idArea, \n".
                    "dett_giorni_lav.idDettGGLav, \n".
                    $selFields." \n".
                    "(".$coalDetail.") AS hasDetail, \n".
                    "progetti.Nome AS Commessa, \n".
                    "progetti.idProgetto \n".
                    "FROM \n".
                        "richieste \n".
                    "INNER JOIN \n".
                        "giorni_lav ON giorni_lav.idRichiesta = richieste.idRichiesta \n".
                    "LEFT OUTER JOIN \n".
                        "dett_giorni_lav ON dett_giorni_lav.idRichiesta = richieste.idRichiesta AND \n".
                        "dett_giorni_lav.Anno = giorni_lav.Anno AND dett_giorni_lav.Mese = giorni_lav.Mese \n".
                    "INNER JOIN \n".
                        "jobs ON jobs.idRichiesta = richieste.idRichiesta \n".
                    "INNER JOIN \n".
                        "personale ON personale.idPersonale = jobs.idPersonale \n".
                    "INNER JOIN \n".
                        "qualifiche ON qualifiche.idQualifica = richieste.idQualifica \n".
                    "INNER JOIN \n".
                        "prog_steps ON prog_steps.idProgStep = richieste.idProgstep \n".
                    "INNER JOIN \n".
                        "aree ON aree.idArea = prog_steps.idArea \n".
                    "INNER JOIN \n".
                        "progetti ON progetti.idProgetto = prog_steps.idProgetto \n";
                $visSql .= $where." ORDER BY progetti.Nome, prog_steps.idProgStep, ".
                        "qualifiche.idQualifica, giorni_lav.Anno, giorni_lav.Mese";
            $allJobs = $this->db->fetchAll($visSql);
            $svgData = $this->calcSvg($allJobs);
//            $visData = $this->calcNewVis($allJobs);
            }
        
//        $visSql .= $where.' ORDER BY ';
//        if($planType == "pers")
//            {
//            $visSql .= "personale.idPersonale, progetti.Nome, prog_steps.idProgStep, qualifiche.idQualifica, ".
//                    "giorni_lav.Anno, giorni_lav.Mese";
//
//            $allJobs = $this->db->fetchAll($visSql);
//            $visData = $this->calcNewVisPers($allJobs);
//            }
//        else
//            {
//            $visSql .= "progetti.Nome, prog_steps.Step, qualifiche.Descrizione, ".
//                    "giorni_lav.Anno, giorni_lav.Mese";
//            $allJobs = $this->db->fetchAll($visSql);
//            $visData = $this->calcNewVis($allJobs);            
//            }

        $retObj = new stdClass();
//        $retObj->visData = $visData;
        $retObj->svgData = $svgData->values;
        $retObj->dataMin = $svgData->min;
        $retObj->dataMax = $svgData->max;
        $retObj->allJobs = $allJobs;
        $retObj->titles = $titles;
        return $retObj;
        }


    private function calcSvgPers($allJobs)
        {
        $retVal = '[';

        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }

        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }

        $minYYMM = '';    
        $maxYYMM = '';    
        if (count($allJobs) > 0)
            {
            $currPersId = 0;
            $currStepId = 0;
            $currQualId = 0;
            $currPerLav = 0;
            $periodOpen = false;
            $i=0;
            foreach ($allJobs as $currJob)
                {    
                $currYYMM = $currJob['Anno'] . sprintf('%02d', $currJob['Mese']);
                if($minYYMM == '' || $minYYMM > $currYYMM) {
                    $minYYMM = $currYYMM;                    
                    }
                if($maxYYMM == '' || $maxYYMM < $currYYMM) {
                    $maxYYMM = $currYYMM;                    
                    }
                    
                if ($currPersId != $currJob['idPersonale'])
                    {
                    $currPersId = $currJob['idPersonale'];
                    if($periodOpen)
                        {
                        $periodOpen = false;
                        $retVal .= "]},\n";
                        }
                    $retVal .= "{ level: 1, expanded: true, label: '" . 
                            addslashes($currJob['Nome'] . ' ' . $currJob['Cognome']) . "'}, \n";
                    $currStepId = 0;
                    $currQualId = 0;
                    }
                if ($currStepId != $currJob['idProgStep'] ||
                    $currCommId != $currJob['idProgetto'])
                    {
                    $currStepId = $currJob['idProgStep'];
                    $currCommId = $currJob['idProgetto'];
                    if($periodOpen)
                        {
                        $periodOpen = false;
                        $retVal .= "]},\n";
                        }
                    $retVal .= "{ level: 2, expanded: true, label: '" . 
                            addslashes($currJob['Commessa'] . ' ' . $currJob['AreaShort']) . "'}, \n";
                    $currQualId = 0;
                    }
                if ($currQualId != $currJob['idQualifica'])
                    {
                    $currQualId = $currJob['idQualifica'];
                    if($periodOpen)
                        {
                        $periodOpen = false;
                        $retVal .= "]},\n";
                        }
                    $retVal .= "{ level: 3, label: '" . 
                            addslashes($currJob['Step']) . 
                            "', perlav: [\n";
                    $periodOpen = true;
                    }                    
                $retVal .= "\t{anno: '" . $currJob['Anno'] . "', ".
                          "mese: '" . sprintf('%02d', $currJob['Mese']) . "', ".
                          "planned: '" . $currJob['Planned'] . "', ".
                          "actual: '" . $currJob['Actual'] .  "', ".
                          "hasDetail: '" . $currJob['hasDetail'] . "', ".
                          "details: [" . $this->listDetail($currJob) . "]}, \n";
                }
            }
        if($periodOpen)
            {
            $retVal .= "]}\n";
            }
        $retVal .= ']';    
        $retObj = new stdClass();
        $retObj->values = $retVal;
        $retObj->min = $minYYMM;
        $retObj->max = $maxYYMM;
        return $retObj;
        }


    private function listDetail($currJob)
        {
        $retArr = array();
        for($day = 1; $day <= 31; $day++)
            {
            $dayZero = sprintf('%02d', $day);
            if((array_key_exists('TipoGG_'.$dayZero, $currJob) && 
                $currJob['TipoGG_'.$dayZero] <> '') || 
                (array_key_exists('FattGG_'.$dayZero, $currJob) &&
                        $currJob['FattGG_'.$dayZero] <> ''))
                { 
                $retArr[] = "'".$dayZero."'";
                }        
            }
        return implode(',', $retArr);    
        }        
        
        
        
    private function calcSvg($allJobs)
        {
        $retVal = '[';
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }

        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }

        $minYYMM = '';    
        $maxYYMM = '';    
        if (count($allJobs) > 0)
            {
            $currCommId = 0;
            $currStepId = 0;
            $currQualId = 0;
            $periodOpen = false;
            $i=0;
            foreach ($allJobs as $currJob)
                {         
                $currYYMM = $currJob['Anno'] . sprintf('%02d', $currJob['Mese']);
                if($minYYMM == '' || $minYYMM > $currYYMM) {
                    $minYYMM = $currYYMM;                    
                    }
                if($maxYYMM == '' || $maxYYMM < $currYYMM) {
                    $maxYYMM = $currYYMM;                    
                    }
                if ($currCommId != $currJob['idProgetto'])
                    {
                    $currCommId = $currJob['idProgetto'];
                    if($periodOpen)
                        {
                        $periodOpen = false;
                        $retVal .= "]},\n";
                        }
                    $retVal .= "{ level: 1, expanded: true, label: '" . 
                            addslashes($currJob['Commessa']) . "', minCol: 0, maxCol: 0}, \n";
                    $currStepId = 0;
                    $currQualId = 0;
                    }
                if ($currStepId != $currJob['idProgStep'])
                    {
                    $currStepId = $currJob['idProgStep'];
                    if($periodOpen)
                        {
                        $periodOpen = false;
                        $retVal .= "]},\n";
                        }
                    $retVal .= "{ level: 2, expanded: true, label: '" . 
                            addslashes($currJob['AreaShort']) . "'}, \n";
                    $currQualId = 0;
                    }
                if ($currQualId != $currJob['idQualifica'])
                    {
                    $currQualId = $currJob['idQualifica'];
                    if($periodOpen)
                        {
                        $periodOpen = false;
                        $retVal .= "]},\n";
                        }
                    $retVal .= "{ level: 3, label: '" . 
                            ($currJob['Step']) . 
                            "', perlav: [\n";
                    $periodOpen = true;
                    }                    
                $retVal .= "\t{anno: '" . $currJob['Anno'] . "', ".
                          "mese: '" . sprintf('%02d', $currJob['Mese']) . "', ".
                          "planned: '" . $currJob['Planned'] . "', ".
                          "actual: '" . $currJob['Actual'] .  "', ".
                          "hasDetail: '" . $currJob['hasDetail'] . "', ".
                          "details: [" . $this->listDetail($currJob) . "]}, \n";
                }
            }
        if($periodOpen)
            {
            $retVal .= "]}\n";
            }
        $retVal .= ']';    
        $retObj = new stdClass();
        $retObj->values = $retVal;
        $retObj->min = $minYYMM;
        $retObj->max = $maxYYMM;
        return $retObj;
        }


//            $arrGroups1 = array();
//            $arrGroups2 = array();
//            $arrGroups3 = array();
//            $arrItems = array();
//            $retGroups1 = '';
//            $retGroups2 = '';
//            $retGroups3 = '';
//            $retItems = '';
//            $i=0;
//            foreach ($allJobs as $currJob)
//                {            
//                if ($currCommId != $currJob['idProgetto'])
//                    {
//                    $currCommId = $currJob['idProgetto'];
//                    $currStepId = 0;
//                    $currQualId = 0;
//                    if($retGroups1 != '')
//                        {
//                        $retGroups1 .= "]},\n";
//                        }
//                    $retGroups1 .= '{ id: "P-'.$currCommId.'",'.
//                                    'content: "'.$currJob['Commessa'].'", '.
//                                    'nestedGroups: [';
//                    }
//                if ($currStepId != $currJob['idProgStep'])
//                    {
//                    $currStepId = $currJob['idProgStep'];
//                    $currQualId = 0;
//                    if(!array_key_exists($currStepId, $arrGroups2))
//                        {
//                        $arrGroups2[$currStepId]=true;
//                        if($retGroups2 != '')
//                            {
//                            $retGroups2 .= "]},\n";
//                            }
//                        $retGroups1 .= '"S-'.$currStepId.'",';
//                        $retGroups2 .= '{ id: "S-'.$currStepId.'",'.
//                                       'content: "'.$currJob['Step'].'",'.
//                                       'nestedGroups: [';
//                        }
//                    }
//                if ($currQualId != $currJob['idQualifica'])
//                    {
//                    $currQualId = $currJob['idQualifica'];
//                    if(!array_key_exists($i, $arrGroups3))
//                        {
//                        $arrGroups3[$i]=true;
//                        if($retGroups3 != '')
//                            {
//                            $retGroups3 .= "},\n";
//                            }
//                        $i++;    
//                        $retGroups2 .= '"Q-'.$i.'",';
//                        $retGroups3 .= '{ id: "Q-'.$i.'",'.
//                                       'content: "'.$currJob['Qualifica'].'"';
//                        }
//                    }
//                
//                if(!array_key_exists($currJob['idGiorniLav'], $arrItems))
//                    {
//                    $arrItems[$currJob['idGiorniLav']]=true;
//                    $retItems .= $this->calcItem($currJob, 'Q-'.$i);
//                    }
//                }
//            }
//        return "groups.add([".$retGroups1."]}]);\n".
//               "groups.add([".$retGroups2."]}]);\n".
//               "groups.add([".$retGroups3."}]);\n".
//               "items.add([".$retItems."]);\n";
//        }
        
        
        
        
        
        
    private function calcNewVisPers($allJobs)
        {
        $retVal = '';
        $arrGroups1 = array();
        $arrGroups2 = array();
        $arrItems = array();
        $retGroups1 = '';
        $retGroups2 = '';
        $retItems = '';
        
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }

        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }
        if (count($allJobs) > 0)
            {
            $currPersId = 0;
            $currStepId = 0;
            $currQualId = 0;
            $i=0;
            foreach ($allJobs as $currJob)
                {            
                if ($currPersId != $currJob['idPersonale'])
                    {
                    $currPersId = $currJob['idPersonale'];
                    if($retGroups1 != '')
                        {
                        $retGroups1 .= "]},\n";
                        }
                    $retGroups1 .= '{ id: "P-'.$currPersId.'",'.
                                   'content: "'.$currJob['Nome'].' '.
                                                $currJob['Cognome'].'",'.
                                   'nestedGroups: [';
                    $currStepId = 0;
                    $currQualId = 0;
                    }
                if ($currStepId != $currJob['idProgStep'] ||
                    $currCommId != $currJob['idProgetto'])
                    {
                    $currStepId = $currJob['idProgStep'];
                    $currCommId = $currJob['idProgetto'];
                    $i++;
                    $retGroups1 .= '"PS-'.$i.'",';
                    if(!array_key_exists($i, $arrGroups2))
                        {
                        $arrGroups2[$i]=true;
                        $retGroups2 .= '{ id: "PS-'.$i.'",'.
                                    'content: "'.$currJob['Commessa'].
                                    ' '.$currJob['Step'].'"}, '."\n";
                        }
                    }
                if(!array_key_exists($currJob['idGiorniLav'], $arrItems))
                    {
                    $arrItems[$currJob['idGiorniLav']]=true;
                    $retItems .= $this->calcItem($currJob, 'PS-'.$i);
                    }
                }
            }
        return "groups.add([".$retGroups1."]}]);\n".
               "groups.add([".$retGroups2."]);\n".
               "items.add([".$retItems."]);\n";
        }


private function calcItem($currJob, $i)
    {    
    $retItem = '';
    $aamm = $currJob['Anno'] . '-' . $currJob['Mese'];
    $start = $aamm . '-01';
    $end = $aamm . '-15';
    $endDate = new DateTime($end);
    $endDate->modify('first day of next month');
    date_sub($endDate, new DateInterval("PT1S"));
    if($currJob['hasDetail'] > 0)
        {
        for($day = 1; $day <= 31; $day++)
            {
            $dayZero = sprintf('%02d', $day);
            if((array_key_exists('TipoGG_'.$dayZero, $currJob) && 
                    $currJob['TipoGG_'.$dayZero] <> '') || 
                    (array_key_exists('FattGG_'.$dayZero, $currJob) &&
                            $currJob['FattGG_'.$dayZero] <> ''))
                { 
                $start = $aamm . '-'.$dayZero;
                $end = $aamm . '-'.$dayZero . ' 23:59:59';
                $retItem .= '{ id: "M'.$currJob['idGiorniLav'].
                        '-D'.$dayZero.'", '.
                        'group: "'.$i.'", '.
                        'content: "&#x25a0;", '.
                        'start: "'.$start.'", '.
                        'end: "'.$end.'", '.
                        "style: \"border: #df3131; background-color: #df3131; color: #df3131;\", \n".
                        'type: "range" }, '."\n";
                }
            }
        }
    else if($currJob['GiorniEff'] > 0)
        {
        $retItem = '{ id: "M-'.$currJob['idGiorniLav'].'", '.
                 'group: "'.$i.'", '.
                 'content: "&#x25a0;", '.
                 'start: "'.$start.'", '.
                 'end: "'.$endDate->format('Y-m-d').'", '.
                 "style: \"border: green; background-color: green; color: green;\", \n".
                 'type: "range" }, '."\n";
        }
    else
        {
        $retItem = '{ id: "M-'.$currJob['idGiorniLav'].'", '.
                 'group: "'.$i.'", '.
                 'content: "&#x25a0;", '.
                 'start: "'.$start.'", '.
                 'end: "'.$endDate->format('Y-m-d').'", '.
                 "style: \"border: #85a3ff; background-color: #85a3ff; color: #85a3ff\", \n".
                 'type: "range" }, '."\n";
        }    
    return $retItem;
    }



        
    private function calcNewVis($allJobs)
        {
        $retVal = '';
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }

        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }
        if (count($allJobs) > 0)
            {
            $currCommId = 0;
            $currStepId = 0;
            $currQualId = 0;
            $arrGroups1 = array();
            $arrGroups2 = array();
            $arrGroups3 = array();
            $arrItems = array();
            $retGroups1 = '';
            $retGroups2 = '';
            $retGroups3 = '';
            $retItems = '';
            $i=0;
            foreach ($allJobs as $currJob)
                {            
                if ($currCommId != $currJob['idProgetto'])
                    {
                    $currCommId = $currJob['idProgetto'];
                    $currStepId = 0;
                    $currQualId = 0;
                    if($retGroups1 != '')
                        {
                        $retGroups1 .= "]},\n";
                        }
                    $retGroups1 .= '{ id: "P-'.$currCommId.'",'.
                                    'content: "'.$currJob['Commessa'].'", '.
                                    'nestedGroups: [';
                    }
                if ($currStepId != $currJob['idProgStep'])
                    {
                    $currStepId = $currJob['idProgStep'];
                    $currQualId = 0;
                    if(!array_key_exists($currStepId, $arrGroups2))
                        {
                        $arrGroups2[$currStepId]=true;
                        if($retGroups2 != '')
                            {
                            $retGroups2 .= "]},\n";
                            }
                        $retGroups1 .= '"S-'.$currStepId.'",';
                        $retGroups2 .= '{ id: "S-'.$currStepId.'",'.
                                       'content: "'.$currJob['Step'].'",'.
                                       'nestedGroups: [';
                        }
                    }
                if ($currQualId != $currJob['idQualifica'])
                    {
                    $currQualId = $currJob['idQualifica'];
                    if(!array_key_exists($i, $arrGroups3))
                        {
                        $arrGroups3[$i]=true;
                        if($retGroups3 != '')
                            {
                            $retGroups3 .= "},\n";
                            }
                        $i++;    
                        $retGroups2 .= '"Q-'.$i.'",';
                        $retGroups3 .= '{ id: "Q-'.$i.'",'.
                                       'content: "'.$currJob['Qualifica'].'"';
                        }
                    }
                
                if(!array_key_exists($currJob['idGiorniLav'], $arrItems))
                    {
                    $arrItems[$currJob['idGiorniLav']]=true;
                    $retItems .= $this->calcItem($currJob, 'Q-'.$i);
                    }
                }
            }
        return "groups.add([".$retGroups1."]}]);\n".
               "groups.add([".$retGroups2."]}]);\n".
               "groups.add([".$retGroups3."}]);\n".
               "items.add([".$retItems."]);\n";
        }
        

//                $start = $currJob['Anno'] . '-' . $currJob['Mese'] . '-01';
//                $end = $currJob['Anno'] . '-' . $currJob['Mese'] . '-15';
//                $endDate = new DateTime($end);
//                $endDate->modify('first day of next month');
//                date_sub($endDate, new DateInterval("P1D"));
//                    $retItems .= '{ id: '.$currJob['idGiorniLav'].', '.
//                             'group: "Q-'.$i.'", '.
//                             'content: "", '.
//                             'start: "'.$start.'", '.
//                             'end: "'.$endDate->format('Y-m-d').'", '.
//                             'type: "range" }, '."\n";
        
    private function getGantt($formData)
        {
        $titles = array();
        $listJobs = isset($formData['idMultiJobs']) ? implode(',', $formData['idMultiJobs']) : '';
        $listQual = isset($formData['idMultiQual']) ? implode(',', $formData['idMultiQual']) : '';
        $listForn = isset($formData['idMultiForn']) ? implode(',', $formData['idMultiForn']) : '';
        $listPers = isset($formData['idMultiPers']) ? implode(',', $formData['idMultiPers']) : '';
        $planType = isset($formData['Type']) ? $formData['Type'] : 'pers';
        if(array_key_exists('startDatealternate', $formData) && $formData['startDatealternate'] != '')
            {
            $startDateArr = explode('/', $formData['startDatealternate']);
            $start = $startDateArr[2].'-'.$startDateArr[1];
            $titles[] = 'From '.$startDateArr[1].'-'.$startDateArr[2];
            $startDate = true;
            }
        else    
            {
            $startDate = false;
            $start = '-'; 
            }
        if(array_key_exists('endDatealternate', $formData) && $formData['endDatealternate'] != '')
            {
//            $endDate = (new DateTime($formData['endDate']))->modify('last day of');
            $endDateArr = explode('/', $formData['endDatealternate']);
            $end = $endDateArr[2].'-'.$endDateArr[1];
            $titles[] = 'To '.$endDateArr[1].'-'.$endDateArr[2];
            $endDate = true;
            }
        else    
            {
            $endDate = false;
            $end = '-'; 
            }
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }
        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }
            
        $ganttSql = "SELECT \n".
                        "jobs.idPersonale, \n".
                        "giorni_lav.Anno, \n".
                        "giorni_lav.Mese, \n".
                        "COALESCE(giorni_lav.GiorniTarget, 0) AS Planned, \n".
                        "COALESCE(giorni_lav.GiorniEff, 0) AS Actual, \n".
                        "jobs.Inizio, \n".
                        "jobs.Fine, \n".
                        "personale.Nome, \n".
                        "personale.Cognome, \n".
                        "qualifiche.Descrizione AS Qualifica, \n".
                        "prog_steps.Step, \n".
                        "dett_giorni_lav.idDettGGLav, \n";
        for($i=1; $i<=31; $i++)
            {
            $ganttSql .= sprintf("dett_giorni_lav.OreGG_%02d,\n ".
                            "dett_giorni_lav.TipoGG_%02d,\n ".
                            "dett_giorni_lav.FattGG_%02d,\n ".
                            "dett_giorni_lav.OreGG_%02d,\n ", $i, $i, $i, $i);
            }
        $ganttSql .=    "progetti.Nome AS Commessa \n".
                    "FROM \n".
                        "richieste \n".
                    "INNER JOIN \n".
                        "giorni_lav ON giorni_lav.idRichiesta = richieste.idRichiesta \n".
                    "LEFT OUTER JOIN \n".
                        "dett_giorni_lav ON dett_giorni_lav.idRichiesta = richieste.idRichiesta AND \n".
                        "dett_giorni_lav.Anno = giorni_lav.Anno AND dett_giorni_lav.Mese = giorni_lav.Mese \n".
                    "INNER JOIN \n".
                        "jobs ON jobs.idRichiesta = richieste.idRichiesta \n".
                    "INNER JOIN \n".
                        "personale ON personale.idPersonale = jobs.idPersonale \n".
                    "INNER JOIN \n".
                        "forn_pers ON forn_pers.idFornPers = personale.idFornPers \n".
                    "INNER JOIN \n".
                        "qualifiche ON qualifiche.idQualifica = richieste.idQualifica \n".
                    "INNER JOIN \n".
                        "prog_steps ON prog_steps.idProgStep = richieste.idProgstep \n".
                    "INNER JOIN \n".
                        "progetti ON progetti.idProgetto = prog_steps.idProgetto \n";
        
        $where = 'WHERE (giorni_lav.GiorniTarget <> 0 OR ' .
                '   giorni_lav.GiorniEff <> 0 OR ' .
                '   dett_giorni_lav.TotGGLav <> 0 OR ' .
                '   dett_giorni_lav.TotGGNR <> 0) ';

        if ($start != '-')
            {
            $where .= ' AND CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) >= "' . $start . '" ';
            }
        if ($end != '-')
            {
            $where .= ' AND CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) <= "' . $end . '" ';
            }
        if($listJobs != '')
            {
            $where .=  ' AND progetti.idProgetto IN ('.$listJobs.')';
            $titles[] = 'Jobs: '.$this->findJobs($listJobs);
            }    
                        
        if($listQual != '')
            {
            $where .=  ' AND qualifiche.idQualifica IN ('.$listQual.')';
            $titles[] = 'Qualifications: '. $this->findQual($listQual);
            }
            
        if($listForn != '')
            {
            $where .=  ' AND forn_pers.idFornPers IN ('.$listForn.')';
            $titles[] = 'Suppliers: '. $this->findSupp($listForn);
            }
            
        if($listPers != '')
            {
            $where .=  ' AND personale.idPersonale IN ('.$listPers.')';
            $titles[] = 'Supervisors: '. $this->findSuper($listPers);
            }    
            
        $where .= " AND (giorni_lav.GiorniTarget > 0 OR giorni_lav.GiorniEff > 0) ";

        $ganttSql .= $where.' ORDER BY ';
        if($planType == "pers")
            {
            $ganttSql .= "personale.Cognome, personale.Nome, progetti.Nome, ".
                    "giorni_lav.Anno, giorni_lav.Mese, ".
                    "prog_steps.Step, qualifiche.Descrizione";

            }
        else
            {
            $ganttSql .= "progetti.Nome, prog_steps.Step, qualifiche.Descrizione, ".
                    "giorni_lav.Anno, giorni_lav.Mese";
            }
            
        $allJobs = $this->db->fetchAll($ganttSql);
        if($planType == "pers")
            {
            $ganttData = $this->calcNewGanttPers($allJobs);
            }
        else
            {
            $ganttData = $this->calcNewGantt($allJobs);            
            }    

        $retObj = new stdClass();
        $retObj->ganttData = $ganttData;
//        var_dump($ganttData);
//        die;
        $retObj->allJobs = $allJobs;
        $retObj->titles = $titles;
        return $retObj;
        }        


    private function findJobs($listJobs)
        {
        $sql = "SELECT CONCAT(JobNumber, ' ', Nome) AS Value, ".
                "CASE WHEN progetti.Inizio > NOW() THEN '2' ".
                "WHEN progetti.Fine < NOW() THEN '1' ELSE '3' END AS `SemOrder` ".
                "FROM progetti WHERE idProgetto IN(".$listJobs.") ".
                "ORDER BY CONCAT(SemOrder, JobNumber +0, JobNumber) DESC" ;
        $allRows = $this->db->fetchAll($sql);
        return $this->joinArr($allRows);
        }

    private function findQual($listQual)
        {
        $sql = "SELECT Descrizione FROM qualifiche ".
                "WHERE idQualifica IN (".$listQual.") ".
                "ORDER BY Descrizione";
        $allRows = $this->db->fetchAll($sql);
        return $this->joinArr($allRows);
        }

    private function findSupp($listForn)
        {
        $sql = "SELECT RagSoc FROM forn_pers ".
                "WHERE idFornPers IN (".$listForn.") ".
                "ORDER BY RagSoc";
        $allRows = $this->db->fetchAll($sql);
        return $this->joinArr($allRows);
        }

    private function findSuper($listPers)
        {
        $sql = "SELECT CONCAT(Cognome, ' ', Nome) FROM personale ".
                "WHERE idPersonale IN (".$listPers.") ".
                "ORDER BY Cognome, Nome";
        $allRows = $this->db->fetchAll($sql);
        return $this->joinArr($allRows);
        }

    private function joinArr($arrVal)
        {
        $retList = '';
        foreach($arrVal as $val)
            {
            if($retList != '')
                {
                $retList .= ', ';
                }
            $retList .= array_values($val)[0];    
            }
        return($retList);    
        }

    private function calcNewGanttPers($allJobs)
        {
        $retVal = '';
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }

        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }
        if (count($allJobs) > 0)
            {
            $currPers = '';
            $currPersId = 0;
            $currComm = '';
            $currStep = '';
            $currQual = '';
            $currName = '';
            $currDesc = '';
            $ganttData = '';
            $isFirst = true;
            $i=1;
            $currId = 1;
            foreach ($allJobs as $currJob)
                {                
                if ($currPers != $currJob['idPersonale'] ||
                    $currStep != $currJob['Step'] ||
                    $currComm != $currJob['Commessa'])
                    {
                    if(!$isFirst)
                        {
                        $endDate = new DateTime($end);
                        $endDate->modify('first day of next month');
                        date_sub($endDate, new DateInterval("P1D"));
                        $ganttData .= 
                            "'".$start."', " .                                  // Start
                            "'".$endDate->format('m/d/Y') . "', ".              // End   
                            "'00ff00', ".                                       // Color
                            "'', ".                                             // Link 
                            "0, ".                                              // Mile 
                            "'', ".                                             // Res  
                            "0, ".                                              // Comp  
                            "0, " .                                             // Group  
                            $currPersId . ", ".                                 // Parent  
                            "1, ".                                              // Open 
                            "'', ".                                             // Depend  
                            "'This is another caption'));\n";                   // Caption 
                        }
                    $isFirst = false;
                    $start = $currJob['Mese'] . '/01/' . $currJob['Anno'];
                    if ($currPers != $currJob['idPersonale'])
                        {
                        $currPersId = $currId;
                        $ganttData .= "g.AddTaskItem(new JSGantt.TaskItem(".
                            $currId++.", '".                                    // Id
                            $currJob['Nome'].' '.$currJob['Cognome']."', ".     // Name
                            "'', ".                                             // Start
                            "'', ".                                             // End   
                            "'ff0000', ".                                       // Color
                            "'', ".                                             // Link 
                            "0, ".                                              // Mile 
                            "'', ".                                             // Res  
                            "0, ".                                              // Comp  
                            "1, ".                                              // Group  
                            "0, ".                                              // Parent  
                            "1, ".                                              // Open 
                            "'', ".                                             // Depend  
                            "'This is the first caption'));\n";                 // Caption    
                        $ganttData .= "g.AddTaskItem(new JSGantt.TaskItem(".
                            $currId++.", ".                                     // Id                                      
                            "'".$currJob['Commessa'].' '.                       // Name
                            $currJob['Step']."', ";                        
                        }
                    else if ($currQual != $currJob['Qualifica'] || 
                        $currComm != $currJob['Commessa'])
                        {
                        $ganttData .= "g.AddTaskItem(new JSGantt.TaskItem(".
                            $currId++.", '".$currJob['Commessa'].' '.
                                            $currJob['Step']."', ";                        
                        }
                    $currPers = $currJob['idPersonale'];
                    $currStep = $currJob['Step'];
                    $currComm = $currJob['Commessa'];
                    }
                else
                    {
                    $end = $currJob['Anno'] . '-' . $currJob['Mese'] . '-15';
                    }    
                }
            $endDate = new DateTime($end);
            $endDate->modify('first day of next month');
            date_sub($endDate, new DateInterval("P1D"));
            $ganttData .= 
                "'".$start."', " .                                  // Start
                "'".$endDate->format('m/d/Y') . "', ".              // End   
                "'00ff00', ".                                       // Color
                "'', ".                                             // Link 
                "0, ".                                              // Mile 
                "'', ".                                             // Res  
                "0, ".                                              // Comp  
                "0, " .                                             // Group  
                $currPersId . ", ".                                 // Parent  
                "1, ".                                              // Open 
                "'', ".                                             // Depend  
                "'This is the last caption'));\n";                  // Caption 
            }
        return $ganttData;    
        }

        
    private function calcNewGantt($allJobs)
        {
        $retVal = '';
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }

        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }
        if (count($allJobs) > 0)
            {
            $currPers = '';
            $currCommId = 0;
            $currComm = '';
            $currStep = '';
            $currQual = '';
            $currName = '';
            $currDesc = '';
            $ganttData = '';
            $isFirst = true;
            $i=1;
            $currId = 1;
            foreach ($allJobs as $currJob)
                {                
                if ($currComm != $currJob['Commessa'] ||
                    $currStep != $currJob['Step'] ||
                    $currQual != $currJob['Qualifica'])
                    {
                    if(!$isFirst)
                        {
                        $endDate = new DateTime($end);
                        $endDate->modify('first day of next month');
                        date_sub($endDate, new DateInterval("P1D"));
                        $ganttData .= 
                            "'".$start."', " .                                  // Start
                            "'".$endDate->format('m/d/Y') . "', ".              // End   
                            "'00ff00', ".                                       // Color
                            "'', ".                                             // Link 
                            "0, ".                                              // Mile 
                            "'', ".                                             // Res  
                            "0, ".                                              // Comp  
                            "0, " .                                             // Group  
                            $currCommId . ", ".                                 // Parent  
                            "1, ".                                              // Open 
                            "'', ".                                             // Depend  
                            "'This is another caption'));\n";                   // Caption 
                        }
                    $isFirst = false;
                    $start = $currJob['Mese'] . '/01/' . $currJob['Anno'];
                    if ($currComm != $currJob['Commessa'])
                        {
                        $currCommId = $currId;
                        $ganttData .= "g.AddTaskItem(new JSGantt.TaskItem(".
                            $currId++.", '".                                    // Id
                            $currJob['Commessa']."', ".     // Name
                            "'', ".                                             // Start
                            "'', ".                                             // End   
                            "'ff0000', ".                                       // Color
                            "'', ".                                             // Link 
                            "0, ".                                              // Mile 
                            "'', ".                                             // Res  
                            "0, ".                                              // Comp  
                            "1, ".                                              // Group  
                            "0, ".                                              // Parent  
                            "1, ".                                              // Open 
                            "'', ".                                             // Depend  
                            "'This is the first caption'));\n";                 // Caption    
                        $ganttData .= "g.AddTaskItem(new JSGantt.TaskItem(".
                            $currId++.", ".                                     // Id                                      
                            "'".$currJob['Step'].' '.                       // Name
                            $currJob['Qualifica']."', ";                        
                        }
                    else if ($currQual != $currJob['Qualifica'] || 
                        $currStep != $currJob['Step'])
                        {
                        $ganttData .= "g.AddTaskItem(new JSGantt.TaskItem(".
                            $currId++.", '".$currJob['Step']."', ";                        
//                            $currId++.", '".$currJob['Commessa'].' '.
                        }
                    $currComm = $currJob['Commessa'];
                    $currStep = $currJob['Step'];
                    $currQual = $currJob['Qualifica'];
                    }
                else
                    {
                    $end = $currJob['Anno'] . '-' . $currJob['Mese'] . '-15';
                    }    
                }
            $endDate = new DateTime($end);
            $endDate->modify('first day of next month');
            date_sub($endDate, new DateInterval("P1D"));
            $ganttData .= 
                "'".$start."', " .                                  // Start
                "'".$endDate->format('m/d/Y') . "', ".              // End   
                "'00ff00', ".                                       // Color
                "'', ".                                             // Link 
                "0, ".                                              // Mile 
                "'', ".                                             // Res  
                "0, ".                                              // Comp  
                "0, " .                                             // Group  
                $currCommId . ", ".                                 // Parent  
                "1, ".                                              // Open 
                "'', ".                                             // Depend  
                "'This is the last caption'));\n";                  // Caption 
            }
        return $ganttData;    
        }
        
        
        
        
               
    private function calcGantt($allJobs)
        {
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }

        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }
        if (count($allJobs) > 0)
            {
            $currPers = '';
            $currComm = '';
            $currStep = '';
            $currQual = '';
            $currName = '';
            $currDesc = '';
            $ganttData = '[';
            $isFirst = false;
            $i=1;
            foreach ($allJobs as $currJob)
                {
                if ($currComm != $currJob['Commessa'] ||
                        $currStep != $currJob['Step'] ||
                        $currQual != $currJob['Qualifica'])
                    {
                    $currComm = $currJob['Commessa'];
                    $currStep = $currJob['Step'];
                    $currQual = $currJob['Qualifica'];
                    if ($ganttData != '[')
                        {
                        $ganttData .= "]},\n";
                        }
                    $ganttData .= '{ "name": "", "desc": "", values: []},' . 
                        '{ "name": "'.$currJob['Commessa'].' - '.$currJob['Step'] . 
                        '","desc": "'.$currJob['Qualifica'].'"  ,"values": [';
//                    $ganttData .= '{ "name": "<span id=\"prova'.$i++.'\">' . 
//                            $currJob['Commessa'] . ' - ' .
//                            $currJob['Step'] . '</span>","desc": "' .
//                            $currJob['Qualifica'] . '"  ,"values": [';
                    $isFirst = true;
                    }
                if ($currJob['idDettGGLav'] > 0)  // Dettaglio
                    {
                    for ($i = 1; $i < 31; $i++)
                        {
                        $currDay = sprintf("%02d", $i);
                        if (isset($currJob['TipoGG_' . $currDay]) && 
                                $currJob['TipoGG_' . $currDay] != '' && 
                                isset($currJob['FattGG_' . $currDay]) && 
                                $currJob['FattGG_' . $currDay] != 'X')
                            {
                            if ($isFirst)
                                {
                                $isFirst = false;
                                }
                            else
                                {
                                $ganttData .= ",\n";
                                }
                            $currData = $currJob['Anno'] . '-' . $currJob['Mese'] . '-' . $currDay;
                            $currOre = (!isset($currJob['OreGG_' . $currDay]) ||
                                    $currJob['OreGG_' . $currDay] == 0) ? 0 : 
                                    $currJob['OreGG_' . $currDay];
                            $currTipo = (!isset($currJob['TipoGG_' . $currDay]) ||
                                    $currJob['TipoGG_' . $currDay] == '') ? '' :
                                    $tipiGG[$currJob['TipoGG_' . $currDay]];
                            $currFatt = (!isset($currJob['FattGG_' . $currDay]) ||
                                    $currJob['FattGG_' . $currDay] == '') ? '' :
                                    $fattGG[$currJob['FattGG_' . $currDay]];
                            $start = new DateTime($currData);
                            $end = new DateTime($currData);
                            $end->modify('next day');
                            date_sub($end, new DateInterval("P1D"));
                            $ganttData .= '{"id": "' .
                                    $currJob['idPersonale'] . '", "from": "' .
                                    $start->format('Y-m-d') . '", "to": "' .
                                    $end->format('Y-m-d') . '", "desc": "", "label": "", "customClass": "Hours: ' .
                                    $currOre . '<br>Type: ' .
                                    $currTipo . '<br>Contract: ' .
                                    $currFatt . ' ", "dep": "b1"}';
                            }
                        }
                    }
                else
                    {
                    if ($isFirst)
                        {
                        $isFirst = false;
                        }
                    else
                        {
                        $ganttData .= ",\n";
                        }
                    $currData = $currJob['Anno'] . '-' . $currJob['Mese'] . '-01';
                    $start = new DateTime($currData);
                    $end = new DateTime($currData);
                    $end->modify('first day of next month');
                    date_sub($end, new DateInterval("P1D"));
                    $ganttData .= '{"id": "' .
                            $currJob['idPersonale'] . '", "from": "' .
                            $start->format('Y-m-d') . '", "to": "' .
                            $end->format('Y-m-d') . '", "desc": "", "label": "", "customClass": "Planned: ' .
                            $currJob['Planned'] . '<br>Actual: ' . $currJob['Actual'] . '", "dep": "b1"}';
                    }
                }
            $ganttData .= ']}]';
            }
        else
            {
            $ganttData = '[]';
            }
        return $ganttData;    
        }

    private function calcGanttPers($allJobs)
        {
        $tipoGGsql = "SELECT SiglaOpzione, Opzione FROM tab_tipo_gg";
        $allTipiGG = $this->db->fetchAll($tipoGGsql);
        $tipiGG = array();
        foreach ($allTipiGG AS $tipo)
            {
            $tipiGG[$tipo['SiglaOpzione']] = $tipo['Opzione'];
            }

        $fattGGsql = "SELECT SiglaOpzione, Opzione FROM tab_fatt_gg";
        $allFattGG = $this->db->fetchAll($fattGGsql);
        $fattGG = array();
        foreach ($allFattGG AS $fatt)
            {
            $fattGG[$fatt['SiglaOpzione']] = $fatt['Opzione'];
            }
        if (count($allJobs) > 0)
            {
            $currPers = '';
            $currComm = '';
            $currStep = '';
            $currQual = '';
            $currName = '';
            $currDesc = '';
            $ganttData = '[';
            $isFirst = false;
            $i=1;
            foreach ($allJobs as $currJob)
                {                
                if ($currPers != $currJob['idPersonale'] ||
                    $currComm != $currJob['Commessa'])
                    {
                    if ($ganttData != '[')
                        {
                        $ganttData .= "]},\n";
                        }
                    if($currPers == $currJob['idPersonale'])
                        {
                        $ganttData .= '{ "name": "",';
                        }
                    else
                        {
                        $ganttData .= '{ "name": "", "desc": "", values: []},' . 
                                '{ "name": "' . $currJob['Cognome'] . ' ' .
                                $currJob['Nome'] . '",';
                        }    

                    $ganttData .= '"desc": "' . $currJob['Commessa'] . '"  ,"values": [';
                    $currPers = $currJob['idPersonale'];
                    $currComm = $currJob['Commessa'];
//                    $ganttData .= '{ "name": "<span id=\"prova'.$i++.'\">' . 
//                            $currJob['Commessa'] . ' - ' .
//                            $currJob['Step'] . '</span>","desc": "' .
//                            $currJob['Qualifica'] . '"  ,"values": [';
                    $isFirst = true;
                    }
                if ($currJob['idDettGGLav'] > 0)  // Dettaglio
                    {
                    for ($i = 1; $i < 31; $i++)
                        {
                        $currDay = sprintf("%02d", $i);
                        if (isset($currJob['TipoGG_' . $currDay]) && 
                                $currJob['TipoGG_' . $currDay] != '' && 
                                isset($currJob['FattGG_' . $currDay]) && 
                                $currJob['FattGG_' . $currDay] != 'X')
                            {
                            if ($isFirst)
                                {
                                $isFirst = false;
                                }
                            else
                                {
                                $ganttData .= ",\n";
                                }
                            $currData = $currJob['Anno'] . '-' . $currJob['Mese'] . '-' . $currDay;
                            $currOre = (!isset($currJob['OreGG_' . $currDay]) ||
                                    $currJob['OreGG_' . $currDay] == 0) ? 0 : 
                                    $currJob['OreGG_' . $currDay];
                            $currTipo = (!isset($currJob['TipoGG_' . $currDay]) ||
                                    $currJob['TipoGG_' . $currDay] == '') ? '' :
                                    $tipiGG[$currJob['TipoGG_' . $currDay]];
                            $currFatt = (!isset($currJob['FattGG_' . $currDay]) ||
                                    $currJob['FattGG_' . $currDay] == '') ? '' :
                                    $fattGG[$currJob['FattGG_' . $currDay]];
                            $start = new DateTime($currData);
                            $end = new DateTime($currData);
                            $end->modify('next day');
                            date_sub($end, new DateInterval("P1D"));
                            $ganttData .= '{"id": "' .
                                    $currJob['idPersonale'] . '", "from": "' .
                                    $start->format('Y-m-d') . '", "to": "' .
                                    $end->format('Y-m-d') . '", "desc": "", "label": "", "customClass": "Hours: ' .
                                    $currOre . '<br>Type: ' .
                                    $currTipo . '<br>Contract: ' .
                                    $currFatt . ' ", "dep": "b1"}';
                            }
                        }
                    }
                else
                    {
                    if ($isFirst)
                        {
                        $isFirst = false;
                        }
                    else
                        {
                        $ganttData .= ",\n";
                        }
                    $currData = $currJob['Anno'] . '-' . $currJob['Mese'] . '-01';
                    $start = new DateTime($currData);
                    $end = new DateTime($currData);
                    $end->modify('first day of next month');
                    date_sub($end, new DateInterval("P1D"));
                    $ganttData .= '{"id": "' .
                            $currJob['idPersonale'] . '", "from": "' .
                            $start->format('Y-m-d') . '", "to": "' .
                            $end->format('Y-m-d') . '", "desc": "", "label": "", "customClass": "Planned: ' .
                            $currJob['Planned'] . '<br>Actual: ' . $currJob['Actual'] . '", "dep": "b1"}';
                    }
                }
            $ganttData .= ']}]';
            }
        else
            {
            $ganttData = '[]';
            }
        return $ganttData;    
        }






    private function lineTot($totals, $label, $offset, $totClass)
        {
        $totGen = 0;
        $maxGen = 0;
        $lineOut = '<tr><td' . $totClass . '>' . $label . '</td>';
        foreach ($totals as $tot)
            {
            $currTot = array_key_exists($offset, $tot) ? $tot[$offset] : 0;
            $lineOut .= '<td' . $totClass . '>' . $currTot . '</td>';
            $totGen += $currTot;
            if ($maxGen < $currTot)
                {
                $maxGen = $currTot;
                }
            }
        $lineOut .= '<td' . $totClass . '>' . $totGen . '</td>';
        $lineOut .= '<td' . $totClass . '>' . $maxGen . '</td></tr>';
        return $lineOut;
        }

    public function outExcelAction()
        {
        require_once 'PHPExcel-1.8/Classes/PHPExcel.php';

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $inputFileName = 'templates/tot-template.xlsx';

        try
            {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
            }
        catch (Exception $e)
            {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' .
                    $e->getMessage());
            }

        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $outExcel = $formData["outTotExcel"];
            $this->view->outExcel = json_decode(stripslashes($outExcel));
            }
        $objPHPExcel->setActiveSheetIndex(0);

        $newwidth = 310;
        $newheight = 65;
        $gdImage = $this->getLogo('logogruppo.png', $newwidth, $newheight);

        // Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setName('Logo image');
        $objDrawing->setDescription('Logo image');
        $objDrawing->setImageResource($gdImage);
        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_PNG);
        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setWidth($newwidth);
        $objDrawing->setHeight($newheight);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        $objDrawing->setCoordinates('A1');

        $fileName = strtoupper(sprintf("%s-TOT-%03d.xls", 1, 1));
        $outputFileName = 'uploads/' . $this->filter_filename($fileName);

        $row = 6;
        $sheet = $objPHPExcel->getActiveSheet();
        $styleArray = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'FFFF00')),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => 'AAAAAA'))),
            'numberformat' => array(
                'code' => '#,##')
        );

        $row = 6;

        $arrExcel = json_decode(stripslashes($outExcel));
        $spanPrevious = 0;
        $spanPrevLine = 0;
        foreach ($arrExcel as $key => $line)
            {
            $col = 0;
            foreach ($line as $index => $value)
                {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $col++;
                $rowIndex = $cell->getRow();
                $cell->setValue(str_replace(".", "", $value));
                $colIndex = $cell->getColumn();
                }
            $row++;
            }

        $maxCols = $col;

        foreach (range(5, $maxCols) as $currCol)
            {
            $sheet->getColumnDimensionByColumn($currCol)->setAutoSize(true);
            }

        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//        $objWriter = PHPExcel_Writer_Excel5_BIFFwriter($objPHPExcel);
//        $objWriter = PHPExcel_Writer_Excel2007($objPHPExcel);
//        $objWriter->setOffice2003Compatibility(true);
        // Download diretto
        //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Cache-Control: max-age=0');
        //
        //$objWriter->save('php://output');
        // File temporaneo

        if (file_exists($outputFileName))
            unlink($outputFileName);
        $objWriter->save($outputFileName);

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . basename($outputFileName));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: public');
        header('Pragma: public');
        header('Content-Length: ' . filesize($outputFileName));
        ob_end_clean();
        readfile($outputFileName);
// Fine file temporaneo
        }

    public function printSheetAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $gglavexcel = json_decode($formData["outTotExcel"]);
            $eventi = $formData["eventi"];
            $commessa = $this->test_input($formData["commessa"]);
            $jobnumber = $this->test_input($formData["jobnumber"]);
            $impianto = $this->test_input($formData["impianto"]);
            $paese = $this->test_input($formData["paese"]);
            $indirizzo = $this->test_input($formData["indirizzo"]);
            $revisione = $this->test_input($formData["revisione"]);
            $mode = $this->test_input($formData["mode"]);
            $totMode = $this->test_input($formData["totmode"]);
            $this->view->gglavexcel = $gglavexcel;
            $this->view->eventi = $eventi;
            $this->view->commessa = $commessa;
            $this->view->jobnumber = $jobnumber;
            $this->view->impianto = $impianto;
            $this->view->paese = $paese;
            $this->view->indirizzo = $indirizzo;
            $this->view->revisione = '';
//            $this->view->revisione = $this->zeroRev($revisione);
            $this->view->mode = $mode;
            $this->view->totMode = $totMode;
            $this->view->formData = $formData;
            }
        else
            {
            return;
            }

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4-L',
            'autoPageBreak' => true,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 5,
            'margin_footer' => 5,
            'setAutoTopMargin' => 'pad',
            'setAutoBottomMargin' => 'pad'
        ]);
        $docName = sprintf("%s-ECS-%03d", $jobnumber, $revisione - 1);
        $this->view->docname = $this->test_input($docName);
        $fileName = $docName . '.pdf';
//        $fileName = sprintf("%s-ECS-%03d.pdf", $jobnumber, $revisione-1);
        $outPrint = $this->filter_filename($fileName);
//        $outPrint = $this->filter_filename($jobnumber . '-ECS-' . $revisione . '.pdf');
        $css1 = file_get_contents(APPLICATION_PATH . '/../public/styles/giorni_lav_grid.css');
        $mpdf->WriteHTML($css1, 1);
        $css2 = file_get_contents(APPLICATION_PATH . '/../public/styles/styles-print.css');
        $mpdf->WriteHTML($css2, 1);
        $html = $this->view->render('reports/print-sheet.phtml');
        echo $html;
        die;
        $mpdf->WriteHTML($html);
        $PdfRender = $mpdf->Output($outPrint, \Mpdf\Output\Destination::DOWNLOAD);
        $this->getResponse()
                ->setHeader('Content-type', 'application/pdf')
                ->setHeader('Pragma', 'public') // required
                ->setHeader('Expires', '0')
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->setHeader('Cache-Control', 'private', false) // required for certain browsers
                ->setHeader('Content-Type', 'application/pdf')
//                ->setHeader('Content-Disposition', 'attachment')
//                ->setHeader('Content-Disposition', 'inline')
//                ->setHeader('Content-Type', '$ctype')
//header("Content-Disposition: attachment; filename=".basename($outputFile).";" );
                ->setHeader('Content-Transfer-Encoding', 'binary')
//          ->setHeader('Content-Length', filesize($sourceFile))
                ->setBody($PdfRender);
        }

    public function totRepGraphAction()
        {
        $this->_helper->layout()->enableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $gglavexcel = json_decode($formData["outTotExcel"]);
            $this->view->gglavexcel = $gglavexcel;
            }
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.dateAxisRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.pointLabels.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.enhancedLegendRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.cursor.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/jquery.jqplot.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/node_modules/html2canvas/dist/html2canvas.min.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/node_modules/es6-promise/es6-promise.auto.min.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/bower_components/printThis/printThis.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jqplot/jquery.jqplot.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/personale_riass.css');
        }

    private function getLogo($logo, $newwidth, $newheight)
        {
        $image = APPLICATION_PATH . '/../public/images/' . $logo;
        list($width, $height) = getimagesize($image);
        $gdImage = imagecreatetruecolor($newwidth, $newheight);
        $source = imagecreatefrompng($image);
        imagecopyresized($gdImage, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        return $gdImage;
        }

    private function test_input($data)
        {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
        }

    private function rgbToColor($value)
        {
        $chunks = explode('(', $value);
        $rgb = explode(')', $chunks[1]);
        $listColors = str_replace(' ', '', $rgb[0]);
        $rgbArr = explode(',', $listColors);
        $retVal = sprintf('%02X%02X%02X', $rgbArr[0], $rgbArr[1], $rgbArr[2]);
        return $retVal;
        }

    private function filter_filename($filename, $beautify = true)
        {
        // sanitize filename
        $filename = preg_replace(
                '~
        [<>:"/\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x', '-', $filename);
        // avoids ".", ".." or ".hiddenFiles"
        $filename = ltrim($filename, '.-');
        // optional beautification
        if ($beautify)
            $filename = $this->beautify_filename($filename);
        // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
        return $filename;
        }

    public function beautify_filename($filename)
        {
        // reduce consecutive characters
        $filename = preg_replace(array(
            // "file   name.zip" becomes "file-name.zip"
            '/ +/',
            // "file___name.zip" becomes "file-name.zip"
            '/_+/',
            // "file---name.zip" becomes "file-name.zip"
            '/-+/'
                ), '-', $filename);
        $filename = preg_replace(array(
            // "file--.--.-.--name.zip" becomes "file.name.zip"
            '/-*\.-*/',
            // "file...name..zip" becomes "file.name.zip"
            '/\.{2,}/'
                ), '.', $filename);
        // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
        $filename = mb_strtoupper($filename, mb_detect_encoding($filename));
//        $filename = mb_strtolower($filename, mb_detect_encoding($filename));
        // ".file-name.-" becomes "file-name"
        $filename = trim($filename, '.-');
        return $filename;
        }

    public function barGraphAction()
        {
        $this->_helper->layout()->enableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $this->setGraphTitle("All Jobs Bars");
        $retVal = $this->buildBarArray();

        $ticks = '[';
        $pw = '[';
        $vendor = '[';
        $external = '[';
        foreach ($retVal['Ticks'] as $key => $current)
            {
            if ($ticks != '[')
                {
                $ticks .= ',';
                $pw .= ',';
                $vendor .= ',';
                $external .= ',';
                }
            $ticks .= '["' . $retVal['Ticks'][$key] . '"]';
            $pw .= $retVal['PW'][$key];
            $vendor .= $retVal['Vendor'][$key];
            $external .= $retVal['External'][$key];
            }
        $ticks .= ']';
        $pw .= ']';
        $vendor .= ']';
        $external .= ']';

        $this->view->ticks = $ticks;
        $this->view->pw = $pw;
        $this->view->vendor = $external;
        $this->view->external = $external;

        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.dateAxisRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.categoryAxisRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.pointLabels.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.barRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.enhancedLegendRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.cursor.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/jquery.jqplot.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/node_modules/html2canvas/dist/html2canvas.min.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jqplot/jquery.jqplot.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/personale_riass.css');
        // action body
        }

    private function buildBarArray()
        {
        $sql = 'SELECT ' .
//                'giorni_lav.Anno, ' .
//                'giorni_lav.Mese, ' .
//                'CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese, 2, "0"), "-01") AS YYMMDD, ' .
//                'giorni_lav.GiorniTarget, ' .
//                'giorni_lav.GiorniEff, ' .
                'progetti.idProgetto, ' .
                'progetti.Nome AS Progetto, ' .
                'progetti.JobNumber AS JobNumber, ' .
                'progetti.JobNumber2 AS JobNumber2, ' .
//                'aree.idArea, '.
//                'aree.Area, '.
//                'aree.AreaShort, '.
                'aree.Vendor, ' .
                'aree.NotInvoiced, ' .
//                'IF( '.
//                '  contratti3.idContratto IS NULL, '.
//                '  contratti2.Contratto, '.
//                '  contratti3.Contratto '.
//                ') AS Contratto, '.
//                'IF( '.
//                '  contratti3.idContratto IS NULL, '.
//                '  contratti2.Sigla, '.
//                '  contratti3.Sigla '.
//                ') AS Sigla, '.
                '( ' .
                '  contratti3.idContratto IS NULL OR contratti3.idContratto = contratti2.idContratto ' .
                ') AS MainContrOwned, ' .
                'IF( ' .
                '  giorni_lav.GiorniEff > 0, ' .
                '  giorni_lav.GiorniEff, ' .
                '  giorni_lav.GiorniTarget ' .
                ') AS GiorniLavorati ' .
                'FROM giorni_lav ' .
                'JOIN richieste ON richieste.idRichiesta = giorni_lav.idRichiesta ' .
                'JOIN prog_steps ON prog_steps.idProgStep = richieste.idProgstep ' .
                'JOIN progetti ON progetti.idProgetto = prog_steps.idProgetto ' .
                'LEFT JOIN ' .
                '  aree ON aree.idProgetto = progetti.idProgetto ' .
                'LEFT JOIN ' .
                '  contratti AS contratti1 ON contratti1.idContratto = richieste.idContratto ' .
                'LEFT JOIN ' .
                '  contratti AS contratti2 ON contratti2.idContratto = progetti.PWMainContractor ' .
                'LEFT JOIN ' .
                '  contratti AS contratti3 ON contratti3.idContratto = aree.idContratto ' .
                'ORDER BY progetti.idProgetto';
        $barPlan = $this->db->fetchAll($sql);
        if (!$barPlan)
            {
            return array();
            }
        $currPlan = '';
        $retArr = array('Ticks' => array(), 'PW' => array(),
            'Vendor' => array(), 'External' => array());
        $currProject = 0;
        foreach ($barPlan AS $plan)
            {
            if ($currProject != $plan['idProgetto'])
                {
                $currProject = $plan['idProgetto'];
                $retArr['Ticks'][$currProject] = substr($plan['Progetto'], 1, 20) . '\n' .
                        $plan['JobNumber'] . ' ' . $plan['JobNumber2'];
                $retArr['PW'][$currProject] = 0;
                $retArr['Vendor'][$currProject] = 0;
                $retArr['External'][$currProject] = 0;
                }
            $retArr['PW'][$currProject] += $plan['GiorniLavorati'];
            $retArr['Vendor'][$currProject] += $plan['GiorniLavorati'];
            $retArr['External'][$currProject] += $plan['GiorniLavorati'];
            }
        return $retArr;
        }

    public function totGraphAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $defaultNamespace->cant2 = $translate->_("Grafico totali");
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'select_graph');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/reports/tot-graph');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();

            $needDays = (array_key_exists('Days', $formData) && $formData['Days']);
            $wantProg = isset($formData['Progressive']) && $formData['Progressive'];
            $listJobs = isset($formData['idMultiJobs']) ? implode(',', $formData['idMultiJobs']) : '';
            $start = '-'; 
            if(array_key_exists('startDatealternate', $formData) && $formData['startDatealternate'] != '')
                {
                $startDateArr = explode('/', $formData['startDatealternate']);
                $start = $startDateArr[2].'-'.$startDateArr[1];
                }
            $end = '-'; 
            if(array_key_exists('endDatealternate', $formData) && $formData['endDatealternate'] != '')
                {
                $endDateArr = explode('/', $formData['endDatealternate']);
                $end = $endDateArr[2].'-'.$endDateArr[1];
                }
            $this->_helper->layout()->enableLayout();
            $this->_helper->viewRenderer->setNoRender(false);
            $defaultNamespace = new Zend_Session_Namespace('Default');
            $currProj = $this->setGraphTitle($listJobs);
            $totRevArr = $this->buildTotArray($listJobs, $start, $end);
            $this->view->wantProg = $wantProg;
            $this->view->currProj = $currProj;
            $this->view->revArr = $totRevArr['retArr'];
            $this->view->revisions = $totRevArr['revisions'];
            $this->view->start = $start;
            $this->view->end = $end;
            $this->view->headScript()->prependFile($this->view->baseUrl() .
                    '/js/plotly-latest.min.js');
            }
        else
            {
            $this->_helper->viewRenderer('jobs_plan');
            $this->view->totGrid = '';
            }
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
   

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
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/bower_components/print-this/print-this.js');

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/assets/prettify.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.filter.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/personale_riass.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/giorni_lav_grid.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/jobs_plan.css');
        }

    private function buildTotArray($listJobs, $start, $end)
        {
        $sql = 'SELECT ' .
                'giorni_lav.Anno, ' .
                'giorni_lav.Mese, ' .
                'CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese, 2, "0"), "-01") AS YYMMDD, ' .
                'giorni_lav.GiorniTarget, ' .
                'giorni_lav.GiorniEff, ' .
                'aree.Vendor, '.
                'aree.NotInvoiced, '.
                '(contratti3.idContratto IS NULL OR contratti3.idContratto = contratti2.idContratto) AS MainContrOwned '.
                'FROM giorni_lav '.
                'JOIN richieste ON richieste.idRichiesta = giorni_lav.idRichiesta '.
                'JOIN prog_steps ON prog_steps.idProgStep = richieste.idProgStep '.
                'JOIN progetti ON progetti.idProgetto = prog_steps.idProgetto '.
                'LEFT JOIN aree ON aree.idArea = prog_steps.idArea '.
                'LEFT JOIN contratti AS contratti3 ON contratti3.idContratto = aree.idContratto '.
                'LEFT JOIN contratti AS contratti1 ON contratti1.idContratto = richieste.idContratto '.
                'LEFT JOIN contratti AS contratti2 ON contratti2.idContratto = progetti.PWMainContractor ';
                
        $where = $whereRev = "WHERE TRUE ";
        if($listJobs != '')
            {
            $where .= 'AND prog_steps.idProgetto IN ('.$listJobs.') ';            
            $whereRev .= 'AND savetot.idProgetto IN ('.$listJobs.') ';            
            }        
        if ($start != '-')
            {
            $where .= ' AND CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) >= "' . $start . '" ';
            $whereRev .= ' AND CONCAT(savetot_dett.Anno, "-", LPAD(savetot_dett.Mese,2,"0")) >= "' . $start . '" ';
            }
        if ($end != '-')
            {
            $where .= ' AND CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) <= "' . $end . '" ';
            $whereRev .= ' AND CONCAT(savetot_dett.Anno, "-", LPAD(savetot_dett.Mese,2,"0")) <= "' . $end . '" ';
            }
        $sql .= $where . 'ORDER BY giorni_lav.Anno, giorni_lav.Mese';
        $allPlan = $this->db->fetchAll($sql);
        if (!$allPlan)
            {
            return array();
            }
        $currPlan = '';
        $retArr = array();
        foreach ($allPlan AS $plan)
            {
            $currKey = $plan['YYMMDD'];
            if($plan['MainContrOwned'])
                {
                $currValue = $plan['GiorniEff'] > 0 ? 
                             $plan['GiorniEff'] : $plan['GiorniTarget'];
                $currContrValue = $plan['NotInvoiced'] > 0 ? 0 : $currValue;
                }
            else
                {                
                $currValue = 0;
                $currContrValue = 0;
                }
            if (!array_key_exists($currKey, $retArr))
                {
                $retArr[$currKey] = array('Current' => $currValue,
                                          'Contract' => $currContrValue); 
                }
            else
                {
                $retArr[$currKey]['Current'] += $currValue; 
                $retArr[$currKey]['Contract'] += $currContrValue; 
                }
            }
        $revisions = $this->buildRevArrGraph($whereRev);    
        return array('retArr' => $retArr, 'revisions' => $revisions);
        }

    private function buildRevArrGraph($whereRev)
        {
        $retArr = array();
        $sqlRev = 'SELECT ' .                
                'savetot.Reference, ' .               
                'savetot.Versione, ' .               
                'savetot_dett.Anno, ' .              
                'savetot_dett.Mese, ' .              
                'CONCAT(savetot_dett.Anno, "-", LPAD(savetot_dett.Mese, 2, "0"), "-01") AS YYMMDD, ' .
                'savetot_dett.Totale, ' .              
                'savetot_dett.TotaleContratto, ' .              
                'savetot_dett.Progressivo, ' .              
                'savetot_dett.ProgrContratto ' .              
            'FROM savetot ' .
            'JOIN savetot_dett ON savetot_dett.idSaveTot = savetot.idSaveTot '.
                $whereRev.
            'ORDER BY savetot.Versione,savetot_dett.Anno,savetot_dett.Mese';
        $allRev = $this->db->fetchAll($sqlRev);
        $currRev = '';
        foreach($allRev AS $rev)
            {
            if($currRev != $rev['Versione'])
                {
                $currRev = $rev['Versione'];
                $retArr[$currRev] = array('Reference'=>$rev['Reference'], 
                    'Versione'=>$rev['Versione'], 'Values'=>array());
                }
            if($rev['Anno'] > 0 && $rev['Mese'] > 0 )
                {
                $key = sprintf("%'.04d-%'.02d-01",$rev['Anno'],$rev['Mese']);    
                $retArr[$currRev]['Values'][$key]['Totale']=$rev['Totale'];              
                $retArr[$currRev]['Values'][$key]['TotaleContratto']=$rev['TotaleContratto'];              
                $retArr[$currRev]['Values'][$key]['Progressivo']=$rev['Progressivo'];    
                $retArr[$currRev]['Values'][$key]['ProgrContratto']=$rev['ProgrContratto'];              
                }
            }            
        return $retArr;    
        }
        
        
        
        
    public function progGraphAction()
        {
        $this->_helper->layout()->enableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->_helper->viewRenderer('/dashboard/totGraph', null, true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
//        $request = $this->getRequest();
//        $currId = $request->getParam('id');
//        $this->findProject($currId);
        $this->setGraphTitle("Progressive");
        if ($this->_request->isPost())
            {
            $request = $this->getRequest();
            $currGGLav = $request->getParam('gglav');
            $currGGLavTot = $request->getParam('gglavtot');
            $currGGLavAllTot = $request->getParam('gglavalltot');
            $currGGLavTotContr = $request->getParam('gglavtotcontr');
            $GGLavArr = json_decode(stripslashes($currGGLav));
            $GGLavArrTot = json_decode(stripslashes($currGGLavTot));
            $GGLavArrAllTot = json_decode(stripslashes($currGGLavAllTot));
            $GGLavArrTotContr = json_decode(stripslashes($currGGLavTotContr));
            }

        $GGTitles = $GGLavArr[0];
        unset($GGLavArr[0]);
        $newGGLavTot = array();
        $newGGLavTotContr = array();
        $prog = 0;
        $progContr = 0;
        $series = array('prog' => array('title' => 'Progressive', 'values' => ''),
            'contr' => array('title' => 'Contract Progressive', 'values' => ''));
        $line1 = '[';
        $line2 = '[';
        for ($y = 1; $y < count($GGLavArrTot) - 4; $y++)
            {
            $newKey = $GGTitles[$y + 4];
            $prog += $GGLavArrTot[$y];
            $progContr += $GGLavArrTotContr[$y];
            $newGGLavTot[$newKey] = $prog;
            $newGGLavTotContr[$newKey] = $progContr;

            $newKeyArr = explode('/', $newKey);
            if ($line1 != '[')
                {
                $line1 .= ',';
                }
            if ($line2 != '[')
                {
                $line2 .= ',';
                }
            $line1 .= "['" . $newKeyArr[1] . '-' .
                    $newKeyArr[0] . "-01'," . $prog . ']';
            $line2 .= "['" . $newKeyArr[1] . '-' .
                    $newKeyArr[0] . "-01'," . $progContr . ']';
            }
        $line1 .= ']';
        $series['prog']['values'] = $line1;
        $line2 .= ']';
        $series['contr']['values'] = $line2;
        $revArr = $this->buildRevArray();
        foreach ($revArr as $serKey => $currSerie)
            {
            $series['prog_' . $serKey] = array('title' => 'Rev. ' . $serKey . ' Prog.', 'values' => '');
            $series['contr_' . $serKey] = array('title' => 'Rev. ' . $serKey . ' Contr.', 'values' => '');
            $line1 = '[';
            $line2 = '[';
            foreach ($currSerie['Values'] as $key => $values)
                {
                if ($line1 != '[')
                    {
                    $line1 .= ',';
                    }
                if ($line2 != '[')
                    {
                    $line2 .= ',';
                    }
                $line1 .= "['" . $key . "'," . $values['Progressivo'] . ']';
                $line2 .= "['" . $key . "'," . $values['ProgrContratto'] . ']';
                }
            $line1 .= ']';
            $series['prog_' . $serKey]['values'] = $line1;
            $line2 .= ']';
            $series['contr_' . $serKey]['values'] = $line2;
            }
        $this->view->series = $series;

        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.dateAxisRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.pointLabels.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.enhancedLegendRenderer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/plugins/jqplot.cursor.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jqplot/jquery.jqplot.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jqplot/jquery.jqplot.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/personale_riass.css');
        // action body
        }

    private function buildRevArray()
        {
        $sql = 'SELECT ' .
                'savetot.Reference, ' .
                'savetot.Versione, ' .
                'savetot_dett.Anno, ' .
                'savetot_dett.Mese, ' .
                'savetot_dett.Totale, ' .
                'savetot_dett.TotaleContratto, ' .
                'savetot_dett.Progressivo, ' .
                'savetot_dett.ProgrContratto ' .
                'FROM savetot ' .
                'JOIN savetot_dett ON savetot_dett.idSaveTot = savetot.idSaveTot ' .
                'ORDER BY savetot.Versione,savetot_dett.Anno,savetot_dett.Mese';
//            'WHERE savetot.idProgetto = "' . $currId . '" '.
        $allRev = $this->db->fetchAll($sql);
        if (!$allRev)
            {
            return array();
            }
        $currRev = '';
        $retArr = array();
        foreach ($allRev AS $rev)
            {
            if ($currRev != $rev['Versione'])
                {
                $currRev = $rev['Versione'];
                $retArr[$currRev] = array('Reference' => $rev['Reference'],
                    'Versione' => $rev['Versione'], 'Values' => array());
                }
            $key = sprintf("%'.04d-%'.02d-01", $rev['Anno'], $rev['Mese']);
            $retArr[$currRev]['Values'][$key]['Totale'] = $rev['Totale'];
            $retArr[$currRev]['Values'][$key]['TotaleContratto'] = $rev['TotaleContratto'];
            $retArr[$currRev]['Values'][$key]['Progressivo'] = $rev['Progressivo'];
            $retArr[$currRev]['Values'][$key]['ProgrContratto'] = $rev['ProgrContratto'];
            }
        return $retArr;
        }
        

    private function setTitle($newTitle)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = $newTitle;
        }
        

    private function setGraphTitle($listJobs)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $projName = array();
        if($listJobs != '')
            {
            $sql = 'SELECT CONCAT(JobNumber, " ", Nome) AS Value, '. 
                   '    CASE WHEN progetti.Inizio > NOW() THEN "2" '. 
                   '         WHEN progetti.Fine < NOW() THEN "1" '. 
                   '         ELSE "3" '.
                   '    END AS SemOrder '.
                   'FROM progetti '.
                   'WHERE idProgetto IN ( '.$listJobs.' ) '.
                   'ORDER BY '.
                   '  CONCAT(SemOrder, '.
                   '  JobNumber +0, '.
                   '  JobNumber) DESC ';
            $listProjects = $this->db->fetchAll($sql);
            foreach ($listProjects as $project)
                {
                $projName[] = $project['Value'];                
                }
            $defaultNamespace->cant2 = implode(', ', $projName)." Totals";
            }
        else
            {
            $defaultNamespace->cant2 = "All Jobs Totals";
            }
        return $projName;    
//        $translate = Zend_Registry::get('translate');
//        $config = Zend_Registry::get('config');
//        $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
//                $defaultNamespace->currProject['Nome'] . " (" .
//                $defaultNamespace->currProject['JobNumber'] . ") - " . $action;
//
        }

    }
