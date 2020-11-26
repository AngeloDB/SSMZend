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
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'select_tot');
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/reports/tot-report/id/' . $currId);
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            $start = $formData['AnnoIn'] . '-' . $formData['MeseIn'];
            $end = $formData['AnnoFi'] . '-' . $formData['MeseFi'];
            $listJobs = implode(',', $formData['idMultiJobs']);
            $listQual = implode(',', $formData['idMultiQual']);
            $needDays = (array_key_exists('Days', $formData) && $formData['Days']);


//       Not Grouped                            
//                    '  IF(qualifiche.idQualifica IS NOT NULL, 1, 0) AS TotQualifiche, '.
//                    '  (COALESCE(dett_giorni_lav.TotGGLav, 0)+ '.
//                    '    CASE WHEN giorni_lav.GiorniEff <> 0 THEN giorni_lav.GiorniEff ELSE giorni_lav.GiorniTarget '.
//                    '    END ) AS TotDays, '.
//       Grouped                            
//                    '  COUNT(qualifiche.idQualifica) AS TotQualifiche, '.
//                    '  SUM(COALESCE(dett_giorni_lav.TotGGLav, 0)) + '.
//                    '   SUM(CASE WHEN giorni_lav.GiorniEff <> 0 THEN '.
//                    '     giorni_lav.GiorniEff ELSE '.
//                    '     giorni_lav.GiorniTarget END) AS TotDays, '.
//                    
//                    'GROUP BY  '.
//                    '  MainContrOwned, '.
//                    '  Qualifica, '.
//                    '  giorni_lav.Anno, '.
//                    '  giorni_lav.Mese '.


            $sql = 'SELECT ' .
                    '  giorni_lav.Anno, ' .
                    '  giorni_lav.Mese, ' .
                    '  CONCAT(LPAD(giorni_lav.Mese, 2, "0"), "/", giorni_lav.Anno) AS GGLavKey, ' .
                    '  CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese,2,"0")) AS AnnoMese, ' .
                    '  qualifiche.Descrizione AS Qualifica, ' .
                    '  pers.Cognome, ' .
                    '  pers.Nome, ' .
                    '  COALESCE(fp1.Tipo, fp2.Tipo) AS PWTipo, ' .
                    '  fp1.RagSoc AS RagSoc, ' .
                    '  fp1.Nome AS FornPers, ' .
                    '  fp1.Tipo AS FPTipo, ' .
                    '  fp2.RagSoc AS CurrRagSoc, ' .
                    '  fp2.Nome AS CurrFornPers, ' .
                    '  fp2.Tipo AS CurrFPTipo, ' .
                    '  IF(pers.idPersonale IS NULL OR pers.Cognome IN ("DELETED", "DEFINED"), 0, 1) AS Assigned, ' .
                    '  IF(qualifiche.idQualifica IS NOT NULL, 1, 0) AS TotQualifiche, ' .
                    '  (COALESCE(dett_giorni_lav.TotGGLav, 0)+ ' .
                    '    CASE WHEN giorni_lav.GiorniEff <> 0 ' .
                    '       THEN giorni_lav.GiorniEff ' .
                    '       ELSE giorni_lav.GiorniTarget ' .
                    '    END ) AS TotDays, ' .
                    '  IF( ' .
                    '    contratti3.idContratto IS NULL, ' .
                    '    contratti2.Contratto, ' .
                    '    contratti3.Contratto ' .
                    '  ) AS Contratto, ' .
                    '  IF( ' .
                    '    contratti3.idContratto IS NULL, ' .
                    '    contratti2.idContratto, ' .
                    '    contratti3.idContratto ' .
                    '  ) AS idContratto, ' .
                    '  IF( ' .
                    '    contratti3.idContratto IS NULL, ' .
                    '    contratti2.Sigla, ' .
                    '    contratti3.Sigla ' .
                    '  ) AS Sigla, ' .
                    '  IF( ' .
                    '    contratti3.idContratto IS NULL, ' .
                    '    contratti2.Sigla = "PW PARTNER", ' .
                    '    contratti3.Sigla = "PW PARTNER" ' .
                    '  ) AS isPwPartner, ' .
                    '  IF( ' .
                    '    contratti3.idContratto IS NULL, ' .
                    '    contratti2.Sigla = "Client", ' .
                    '    contratti3.Sigla = "Client" ' .
                    '  ) AS isClient, ' .
                    '  ( ' .
                    '    contratti3.idContratto IS NULL OR contratti3.idContratto = contratti2.idContratto ' .
                    '  ) AS MainContrOwned, ' .
                    '  contratti3.idContratto, ' .
                    '  (contratti3.Sigla != "Client" && contratti3.Sigla != "PW_PARTNER") AS isPwoe, ' .
                    '  aree.Vendor, ' .
                    '  aree.NotInvoiced ' .
                    'FROM ' .
                    '  giorni_lav ' .
                    'JOIN ' .
                    '  richieste ON richieste.idRichiesta = giorni_lav.idRichiesta ' .
                    'JOIN ' .
                    '  qualifiche ON qualifiche.idQualifica = richieste.idQualifica ' .
                    'JOIN ' .
                    '  prog_steps ON prog_steps.idProgStep = richieste.idProgstep ' .
                    'JOIN ' .
                    '  progetti ON progetti.idProgetto = prog_steps.idProgetto ' .
                    'JOIN ' .
                    '  aree ON aree.idArea = prog_steps.idArea ' .
                    'LEFT JOIN ' .
                    '  jobs ON jobs.idRichiesta = richieste.idRichiesta ' .
                    'LEFT JOIN ' .
                    '  personale AS pers ON pers.idPersonale = jobs.idPersonale ' .
                    'LEFT JOIN ' .
                    '  pers_forn_pers AS pfp1 ON pfp1.idPersonale = jobs.idPersonale AND pfp1.idProgetto = progetti.idProgetto AND DATE( ' .
                    '    CONCAT(giorni_lav.Anno, ' .
                    '    "-", ' .
                    '    giorni_lav.Mese, ' .
                    '    "-15") ' .
                    '  ) BETWEEN pfp1.Dal AND pfp1.Al ' .
                    'LEFT JOIN ' .
                    '  forn_pers AS fp1 ON fp1.idFornpers = pfp1.idFornPers ' .
                    'LEFT JOIN ' .
                    '  forn_pers AS fp2 ON fp2.idFornpers = pers.idFornPers ' .
                    'LEFT JOIN ' .
                    '  contratti AS contratti1 ON contratti1.idContratto = richieste.idContratto ' .
                    'LEFT JOIN ' .
                    '  contratti AS contratti2 ON contratti2.idContratto = progetti.PWMainContractor ' .
                    'LEFT JOIN ' .
                    '  contratti AS contratti3 ON contratti3.idContratto = aree.idContratto ' .
                    'LEFT JOIN ' .
                    '  dett_giorni_lav ON ' .
                    '  dett_giorni_lav.idRichiesta = richieste.idRichiesta AND ' .
                    '  dett_giorni_lav.Anno = giorni_lav.Anno AND ' .
                    '  dett_giorni_lav.Mese = giorni_lav.Mese ';

            $where = 'WHERE (giorni_lav.GiorniTarget <> 0 OR ' .
                    '   giorni_lav.GiorniEff <> 0 OR ' .
                    '   dett_giorni_lav.TotGGNR <> 0) ';
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

//                '  PWTipo,  '.
//                '  Assigned,  '.
//                '  Vendor  ';
//                
//                '  PWTipo,  '.
//                '  Assigned,  '.
//                '  Vendor  '.

            $tot = $this->db->fetchAll($sql);
            $now = new DateTime();
            $year = $now->format("Y");
            if ($start != '-')
                {
                $startDate = new DateTime($start . '-01');
                }
            else
                {
                $startDate = new DateTime($year . '-01-01');
                }
            if ($end != '-')
                {
                $endDate = new DateTime($end . '-01');
                }
            else
                {
                $endDate = new DateTime($year . '-12-31');
                }
            $interval = new DateInterval('P1M');
            $endDate->add($interval);
            $period = new DatePeriod($startDate, $interval, $endDate);

            $selectedPW = array();
            foreach($formData['idMultiSearch'] as $pw)
                {
                if(is_numeric($pw))
                    {
                    $selectedPW[] = $pw;
                    }
                }
            $listPW = implode(',', $selectedPW);    
            $monthArr = array();
            $monthTot = array();
            $diffTot = array('pwoe' => 0, 'vend' => 0,
                'pwPartner' => 0, 'cons' => 0, 'MCpw' => 0, 'MCvend' => 0,
                'MCnotAss' => 0, 'MCcons' => 0);
            $sqlContr = 'SELECT idContratto, ' .
                    '       CONCAT(Sigla, " ", Contratto) AS Contr ' .
                    'FROM contratti WHERE Sigla != "Client" AND idContratto IN ('.
                    $listPW.')';
            $pwContr = $this->db->fetchAll($sqlContr);
            foreach ($pwContr as $pwLine)
                {
                $diffTot[$pwLine['idContratto']] = 0;
                $diffTot['vend_'.$pwLine['idContratto']] = 0;
                }
            $diffTotObject = new ArrayObject($diffTot);
            foreach ($period as $dt)
                {
                $monthArr[$dt->format('Y-m')] = '';
                $monthTot[$dt->format('Y-m')] = $diffTotObject->getArrayCopy();
//                $monthTot[$dt->format('Y-m')] = array('pwoe'=>0, 'vend'=>0, 
//                    'pwPartner'=>0, 'cons'=>0, 'MCpw'=>0, 'MCvend'=>0, 
//                    'MCnotAss'=>0, 'MCcons'=>0);
                }

            $totMode = '';
            $arrResult = array();
            foreach ($tot as $line)
                {
                if ($line['isClient'] == 0)
                    {
                    if (!array_key_exists($line['Qualifica'], $arrResult))
                        {
                        $arrResult[$line['Qualifica']] = $monthArr;
                        }

//                    if(!$line['MainContrOwned'])
//                        {
//                        $totMode='cons';                    
//                        }                    
//                    else if($line['Vendor'] > 0)
//                        {
//                        $totMode='vend';
//                        }                    
//                    else
//                        {
//                        $totMode='pw';
//                        }
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

                    if (array_search($totMode, $formData['idMultiSearch']) !== FALSE)
                        {
                        if ($needDays)
                            {
                            $arrResult[$line['Qualifica']][$line['AnnoMese']] += $line['TotDays'];
                            $monthTot[$line['AnnoMese']][$line['idContratto']] += $line['TotDays'];
                            if ($totMode != 'pwoe' || $totPwoe)
                                {
                                $monthTot[$line['AnnoMese']][$totMode] += $line['TotDays'];
                                }
                            }
                        else
                            {
                            $arrResult[$line['Qualifica']][$line['AnnoMese']] += $line['TotQualifiche'];
                            $monthTot[$line['AnnoMese']][$line['idContratto']] += $line['TotQualifiche'];
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
            $defaults['AnnoIn'] = $defaults['AnnoFi'] = date('Y');
            $defaults['MeseIn'] = '01';
            $defaults['MeseFi'] = '12';
            $form->populate($defaults);
            $this->view->totGrid = '';
            }
        $this->view->javascript = $currForm->getJavascript();
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getEditTitle();
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/giorni_lav_grid.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        // action body
        }

    private function buildTotGrid($arrResult, $totals, $pwContr)
        {
        $outTable = '<table id="totGrid" border="1">';
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
                $totMonth += $monthValue;
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
        foreach($pwContr as $pw)
            {
            $totClass = ($numRow++ % 2 == 0) ? ' class="td_nctot"' : ' class="td_nctot2"';
            $outTable .= $this->lineTot($totals, $pw['Contr'], $pw['idContratto'], $totClass);            
            }
//        $totClass = ($numRow++ % 2 == 0) ? ' class="td_nctot"' : ' class="td_nctot2"';
//        $outTable .= $this->lineTot($totals, 'Consultants', 'cons', $totClass);
//        
//        $outTable .= '<tr><td'.$totClass.'>Vendor</td>';
//        foreach ($totals as $tot)
//            {
//            $outTable .= '<td'.$totClass.'>'.$tot['vend'].'</td>';
//            }            
//        $outTable .= '<td'.$totClass.'>'.$totGen.'</td>';
//        $outTable .= '</tr>';
//
//        $outTable .= '<tr><td'.$totClass.'>Paul Wurth</td>';
//        foreach ($totals as $tot)
//            {
//            $outTable .= '<td'.$totClass.'>'.$tot['pw'].'</td>';
//            }            
//        $outTable .= '<td'.$totClass.'>'.$totGen.'</td>';
//        $outTable .= '</tr>';
//        $outTable .= '<tr><td'.$totClass.'>Consultants</td>';
//        foreach ($totals as $tot)
//            {
//            $outTable .= '<td'.$totClass.'>'.$tot['cons'].'</td>';
//            }            
//        $outTable .= '<td'.$totClass.'>'.$totGen.'</td>';
//        $outTable .= '</tr>';
//
        $outTable .= '</tfoot></table>';
        return $outTable;
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
        $this->_helper->layout()->enableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $this->setGraphTitle("All Jobs Totals");
        $series = array('Planned' => '[', 'Actual' => '[');
        $revArr = $this->buildTotArray();
        foreach ($revArr as $mKey => $currMonth)
            {
            $series['Planned'] .= "['" . $mKey . "'," . $currMonth['Planned'] . '],';
            $series['Actual'] .= "['" . $mKey . "'," . $currMonth['Actual'] . '],';
            }
        $series['Planned'] .= ']';
        $series['Actual'] .= ']';
//        var_dump($series);
//        die;
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
        // action body
        }

    private function buildTotArray()
        {
        $sql = 'SELECT ' .
                'giorni_lav.Anno, ' .
                'giorni_lav.Mese, ' .
                'CONCAT(giorni_lav.Anno, "-", LPAD(giorni_lav.Mese, 2, "0"), "-01") AS YYMMDD, ' .
                'giorni_lav.GiorniTarget, ' .
                'giorni_lav.GiorniEff ' .
                'FROM giorni_lav ' .
                'ORDER BY giorni_lav.Anno, giorni_lav.Mese';
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
            if (!array_key_exists($currKey, $retArr))
                {
                $retArr[$currKey] = array('Planned' => $plan['GiorniTarget'],
                    'Actual' => $plan['GiorniEff']);
                }
            else
                {
                $retArr[$currKey]['Planned'] += $plan['GiorniTarget'];
                $retArr[$currKey]['Actual'] += $plan['GiorniEff'];
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

    private function setGraphTitle($action)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->cant2 = $action;
//        $translate = Zend_Registry::get('translate');
//        $config = Zend_Registry::get('config');
//        $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
//                $defaultNamespace->currProject['Nome'] . " (" .
//                $defaultNamespace->currProject['JobNumber'] . ") - " . $action;
//
        }

    }
