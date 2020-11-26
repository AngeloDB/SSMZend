<?php

class OutExcelController extends Zend_Controller_Action
    {

    public function init()
        {
        /* Initialize action controller here */
        }

    public function indexAction()
        {
        // action body
        }

    public function outSheetAction()
        {
//    ini_set('display_errors', 1);
//    ini_set('display_startup_errors', 1);
//    error_reporting(E_ALL);
        require_once 'PHPExcel-1.8/Classes/PHPExcel.php';

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $inputFileName = 'templates/plan-template.xlsx';
//        $outputFileName = 'uploads/file.xslx';

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
            $gglavexcel = $formData["gglavexcel"];
            $strStatus = $formData["status"];
            $eventi = $formData["eventi"];
            $commessa = $this->test_input($formData["commessa"]);
            $jobnumber = $this->test_input($formData["jobnumber"]);
            $impianto = $this->test_input($formData["impianto"]);
            $paese = $this->test_input($formData["paese"]);
            $indirizzo = $this->test_input($formData["indirizzo"]);
            $revisione = $this->test_input($formData["revisione"]);
            $mode = $this->test_input($formData["mode"]);
            }
        $status = json_decode(stripslashes($strStatus));

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B2', $jobnumber)
                ->setCellValue('B3', $commessa)
                ->setCellValue('B4', $impianto)
                ->setCellValue('D2', $paese)
                ->setCellValue('D3', $indirizzo)
                ->setCellValue('E4', $this->zeroRev($revisione))
                ->setCellValue('E2', 'CONS. DATE:');

        $newwidth = 310;
        $newheight = 65;
        $gdImage  = $this->getLogo('logogruppo.png', $newwidth, $newheight);
        
        // Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setName('Logo image');$objDrawing->setDescription('Logo image');
        $objDrawing->setImageResource($gdImage);
        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_PNG);
        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setWidth($newwidth);
        $objDrawing->setHeight($newheight);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        $objDrawing->setCoordinates('A1');

        $fileName = strtoupper(sprintf("%s-ECS-%03d.xls", $jobnumber, $revisione-1));
        $outputFileName = 'uploads/' . $this->filter_filename($fileName);
        
//        $outputFileName = 'uploads/'.
//                $this->filter_filename($jobnumber . '-ECS-' . $commessa. '.xls');
        $row = 6;
        $sheet = $objPHPExcel->getActiveSheet();
        if ($mode == 'cash')
            {
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
                    'code' => '#,##0.00')
            );
            }
        else
            {
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
            }
        $row = 4;
        $evToExcel = json_decode(stripslashes($eventi));
        if (is_array($evToExcel))
            {
            foreach ($evToExcel as $key => $line)
                {
                $col = $line->MesiOffset + 5;
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $cell->setValue($line->Evento);
                }
            }

        $row = 5;

//        $tmpGGToExcel = stripslashes($gglavexcel);
//        echo $tmpGGToExcel;
//        echo '<br><br><br>';
        $GGToExcel = json_decode(stripslashes($gglavexcel));
//        var_dump(json_decode($tmpGGToExcel));
//        echo '<br><br><br>';
//        echo json_last_error();
//        echo '<br><br><br>';
//        echo json_last_error_msg();
//        
//        die;
        $spanPrevious = 0;
        $spanPrevLine = 0;
        foreach ($GGToExcel as $key => $line)
            {
//            array_slice($line, 0, -3);
            if (count($line) > 1)
                {
                $col = 0;
                if($key == 1 && $status->my)
                    {
                    $col += 1;
                    // if(!$status->cc) $col++;
                    // if(!$status->pc) $col++;
                    // if(!$status->qual) $col++;
                    // if(!$status->sup) $col++;
                    }
                foreach ($line as $index => $value)
                    {
                    $cell = $sheet->getCellByColumnAndRow($col+$spanPrevLine, $row);
                    $col++;
                    $rowIndex = $cell->getRow();
                    $colIndex = $cell->getColumn();
                    $currColor = $this->rgbToColor($value->color);
                    $currTextColor = $this->rgbToColor($value->textColor);
                    if ($currColor != '000000')
                        {
                        $styleArray['fill']['startcolor']['rgb'] = $currColor;
                        $styleArray['font']['color']['rgb'] = $currTextColor;
                        $styleArray['font']['alignment'] = "right";
                        $cell->setValue(str_replace(".", "", $value->value));
                        }
                    $cell->getStyle()->applyFromArray($styleArray);
                    if (isset($value->rowSpan) && $value->rowSpan > 1)
                        {
                        $spanPrevious++;
                        }
                    if (isset($value->span) && $value->span > 1)
                        {
                        $col += ($value->span-2);
                        $endCol = PHPExcel_Cell::stringFromColumnIndex($col);  // + $value->span-1
                        $rangeSpan = $colIndex . $rowIndex . ':' . $endCol . $rowIndex;
                        $sheet->mergeCells($rangeSpan);
                        $sheet->getStyle($rangeSpan)->applyFromArray($styleArray);
                        $col++;
                        }
                    }
                }
            if($spanPrevious > 0)
                {
                $spanPrevLine = $spanPrevious-3;
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
            $gglavexcel = json_decode($formData["gglavexcel"]);
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
            $this->view->revisione = $this->zeroRev($revisione);
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
        $docName = sprintf("%s-ECS-%03d", $jobnumber, $revisione-1);
        $this->view->docname = $this->test_input($docName);
        $fileName = $docName.'.pdf';
//        $fileName = sprintf("%s-ECS-%03d.pdf", $jobnumber, $revisione-1);
        $outPrint = $this->filter_filename($fileName);
//        $outPrint = $this->filter_filename($jobnumber . '-ECS-' . $revisione . '.pdf');
        $css1 = file_get_contents(APPLICATION_PATH . '/../public/styles/giorni_lav_grid.css');
        $mpdf->WriteHTML($css1, 1);
        $css2 = file_get_contents(APPLICATION_PATH . '/../public/styles/styles-print.css');
        $mpdf->WriteHTML($css2, 1);
        $html = $this->view->render('out-excel/print-sheet.phtml');
//        echo $html;
//        die;
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

    private function filter_filename($filename, $beautify = true)
        {
        // sanitize filename
        $filename = preg_replace(
                '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
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

    function beautify_filename($filename)
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

    }
