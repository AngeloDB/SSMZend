<?php

class Custom_MyExcel
    {

    private $objPHPExcel;
    private $styleArray;
    private $status;
    private $sheet;

    public function init()
        {
        require_once 'PHPExcel-1.8/Classes/PHPExcel.php';        
        }

    public function initSheet($inputFileName)
        {
//        $inputFileName = 'templates/plan-template.xlsx';
//        $outputFileName = 'uploads/file.xslx';
        try
            {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $this->objPHPExcel = $objReader->load($inputFileName);
            }
        catch (Exception $e)
            {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' .
                    $e->getMessage());
            }
        return $this->objPHPExcel;
        }

    public function setStyle($mode)
        {
        if ($mode == 'cash')
            {
            $this->styleArray = array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array(
                        'rgb' => 'FFFF00')),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'AAAAAA'))),
                'numberformat' => array(
                    'code' => '#,##0.00')
                );
            }
        else
            {
            $this->styleArray = array(
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
            }
        }

    public function setTitles($formData, $logo)
        {
        $commessa = $this->test_input($formData["commessa"]);
        $jobnumber = $this->test_input($formData["jobnumber"]);
        $impianto = $this->test_input($formData["impianto"]);
        $paese = $this->test_input($formData["paese"]);
        $indirizzo = $this->test_input($formData["indirizzo"]);
        $revisione = $this->test_input($formData["revisione"]);
        $mode = $this->test_input($formData["mode"]);

        $this->setStyle($mode);
        $this->objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B2', $jobnumber)
                ->setCellValue('B3', $commessa)
                ->setCellValue('B4', $impianto)
                ->setCellValue('D2', $paese)
                ->setCellValue('D3', $indirizzo)
                ->setCellValue('E4', $this->zeroRev($revisione))
                ->setCellValue('E2', 'CONS. DATE:');

        $newwidth = 310;
        $newheight = 65;
        $gdImage = $this->getLogo($logo, $newwidth, $newheight);

        // Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setName('Logo image');
        $objDrawing->setDescription('Logo image');
        $objDrawing->setImageResource($gdImage);
        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_PNG);
        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setWidth($newwidth);
        $objDrawing->setHeight($newheight);
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());
        $objDrawing->setCoordinates('A1');
        }

    public function fillSheet($formData, $logo)
        {
        $this->setTitles($formData, $logo);
        $this->sheet = $this->objPHPExcel->getActiveSheet();
        $strStatus = $formData["status"];
        $eventi = $formData["eventi"];
        $gglavexcel = $formData["gglavexcel"];
        $status = json_decode(stripslashes($strStatus));
        $this->status = $status;
        $mode = $this->test_input($formData["mode"]);
//        $this->numTitles = $this->countTitles($status);
//        $visTitles = $this->countTitles($status);

        $row = 4;
        $evToExcel = json_decode(stripslashes($eventi));
        if (is_array($evToExcel))
            {
            foreach ($evToExcel as $key => $line)
                {
                $col = $line->MesiOffset + 5;
                $cell = $this->sheet->getCellByColumnAndRow($col, $row);
                $cell->setValue($line->Evento);
                }
            }

        $row = 5;

        $allExcel = json_decode(stripslashes($gglavexcel));
        if ($status->my == TRUE)
            {
            $headExcel = array_slice($allExcel, 0, 2, TRUE);
            $restExcel = array_slice($allExcel, 2, NULL, TRUE);
            }
        else
            {
            $headExcel = array_slice($allExcel, 0, 2, TRUE);            
            $restExcel = array_slice($allExcel, 2, NULL, TRUE);
            }    
                        
        if($mode == 'plan')
            {
            $footExcel = array_slice($restExcel, -4, NULL, TRUE);
            $bodyExcel = array_slice($restExcel, 0, count($restExcel)-4, TRUE);
            }    
        else
            {
            $footExcel = array_slice($restExcel, -2, NULL, TRUE);            
            $bodyExcel = array_slice($restExcel, 0, count($restExcel)-2, TRUE);
            }    
//        echo '<pre>';
//        print_r($headExcel);
//        print_r($bodyExcel);
//        print_r($footExcel);
//        echo '</pre>';        
//        die();
//        $GGToExcel = json_decode(stripslashes($gglavexcel));
        $row = $this->setGroup($headExcel, $row);
        $row = $this->setGroup($bodyExcel, $row);
        $row = $this->setGroup($footExcel, $row);

        return $this->objPHPExcel;
        }
        
        
//    private function setGroup($GGToExcel, $status, $row)
    private function setGroup($GGToExcel, $row)
        {
        $spanPrevious = 0;
        $spanPrevLine = 0;
        foreach ($GGToExcel as $key => $line)
            {
            if (count($line) > 1)
                {
                $col = 0;
                if ($key == 1 && $this->status->my)
                    {
                    $col = 1;
//                    $numTitles = $this->countTitles($this->status);
//                    $col = $numTitles-1;
//                    if (!$status->cc)
//                        $col++;
//                    if (!$status->pc)
//                        $col++;
//                    if (!$status->qual)
//                        $col++;
//                    if (!$status->sup)
//                        $col++;
                    }
                foreach ($line as $index => $value)
                    {
                    $cell = $this->sheet->getCellByColumnAndRow($col + $spanPrevLine, $row);
                    $col++;
                    $rowIndex = $cell->getRow();
                    $colIndex = $cell->getColumn();
                    $currColor = $this->rgbToColor($value->color);
                    $currTextColor = $this->rgbToColor($value->textColor);
                    if ($currColor != '000000')
                        {
                        $this->styleArray['fill']['startcolor']['rgb'] = $currColor;
                        $this->styleArray['font']['color']['rgb'] = $currTextColor;
                        $this->styleArray['font']['alignment'] = "right";
                        $cell->setValue(str_replace(".", "", $value->value));
                        }
                    $cell->getStyle()->applyFromArray($this->styleArray);
                    if (isset($value->rowSpan) && $value->rowSpan > 1)
                        {
                        $spanPrevious++;
                        }
                    if (isset($value->span) && $value->span > 1)
                        {
                        $col += ($value->span - 2);
                        $endCol = PHPExcel_Cell::stringFromColumnIndex($col);  // + $value->span-1
                        $rangeSpan = $colIndex . $rowIndex . ':' . $endCol . $rowIndex;
                        $this->sheet->mergeCells($rangeSpan);
                        $this->sheet->getStyle($rangeSpan)->applyFromArray($this->styleArray);
                        $col++;
                        }
                    }
                }
            if ($spanPrevious > 0)
                {
                $spanPrevLine = $spanPrevious - 3;
                }
            else
                {
                $spanPrevLine = 0;
                }
            $spanPrevious = 0;
            $row++;
            }

        $maxCols = $col;

        foreach (range(5, $maxCols) as $currCol)
            {
            $this->sheet->getColumnDimensionByColumn($currCol)->setAutoSize(true);
            }
        return $row;
        }
        
    public function getSheet()
        {
        return $this->objPHPExcel;
        }
        
    public function outSheet($outputFileName)
        {
        $this->objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
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
        return $this->objPHPExcel;
        }
        
    private function countTitles($status)
        {
        $count = 0;
        if ($status->cc)
            {
            $count++;
            }
        if ($status->pc)
            {
            $count++;
            }
        if ($status->qual)
            {
            $count++;            
            }
        if ($status->sup)
            {
            $count++;
            }
        return $count;
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

    private function zeroRev($num)
        {
        return 'Rev.' . ($num > 0 ? $num - 1 : 'C');
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

    private function stripInvalid($inString)
        {
        $search = array(chr(0xC2) . chr(0xA0), // c2a0; Alt+255; Alt+0160; Alt+511; Alt+99999999;
            chr(0xC2) . chr(0x90), // c290; Alt+0144
            chr(0xC2) . chr(0x9D), // cd9d; Alt+0157
            chr(0xC2) . chr(0x81), // c281; Alt+0129
            chr(0xC2) . chr(0x8D), // c28d; Alt+0141
            chr(0xC2) . chr(0x8F), // c28f; Alt+0143
            chr(0xC2) . chr(0xAD), // cdad; Alt+0173
            chr(0xAD)); // Soft-Hyphen; AD
        $outString = str_replace($search, ' ', $inString);
        if ($outString == '')
            $outString = '&nbsp;';
        return $outString;
        }

    }