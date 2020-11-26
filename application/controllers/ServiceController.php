<?php

class ServiceController extends Zend_Controller_Action
    {

    public function init()
        {
        /* Initialize action controller here */
        }

    public function indexAction()
        {
        // action body
        phpinfo();
        }

    public function listActionsAction()
        {
        $front = $this->getFrontController();
        $acl = array();

        foreach ($front->getControllerDirectory() as $module => $path)
            {

            foreach (scandir($path) as $file)
                {

                if (strstr($file, "Controller.php") !== false)
                    {

                    include_once $path . DIRECTORY_SEPARATOR . $file;

                    foreach (get_declared_classes() as $class)
                        {

                        if (is_subclass_of($class, 'Zend_Controller_Action'))
                            {

                            $controller = strtolower(substr($class, 0, strpos($class, "Controller")));
                            $actions = array();

                            foreach (get_class_methods($class) as $action)
                                {

                                if (strstr($action, "Action") !== false)
                                    {
                                    $actions[] = $action;
                                    }
                                }
                            }
                        }

                    $acl[$module][$controller] = $actions;
                    }
                }
            }
        var_dump($acl);
        }

    public function testSvgAction()
        {
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                    '/js/svg.min.js');
        }

    public function readAclAction()
        {
        $configPath = APPLICATION_PATH . '/configs/acl.xml';
        $frontend = array(
            'master_files' => array(
                $configPath),
            'automatic_serialization' => true);
        $backend = array();
        $cache = Zend_Cache::factory('File', 'Memcached', $frontend, $backend);
        if (!($config = $cache->load('configXml')))
            {
            $config = new Zend_Config_Xml($configPath, 'acl');
            $cache->save($config, 'configXml');
            }
        else
            {
            echo 'Cached!<br>';
            }
        $myXml = simplexml_load_file(APPLICATION_PATH .
                '/configs/acl.xml');
        $acl2 = json_decode(json_encode($myXml), TRUE);
        $acl = $config->toArray();
        echo '<pre>';
        print_r($acl);
        print_r($acl2);
        echo '</pre>';
        }

    public function accessDeniedAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $this->view->noAllow = $defaultNamespace->noAllow;
        $this->view->noAllowController = $defaultNamespace->noAllowController;
        $this->view->noAllowAction = $defaultNamespace->noAllowAction;
        $this->view->noAllowParameters = $defaultNamespace->noAllowParameters;
        $this->view->currUserLevel = $defaultNamespace->area;
        }

    public function excelDemoAction()
        {
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Europe/London');

        $inputFileName = 'templates/plan-template.xlsx';

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

//        $sheet = $objPHPExcel->getSheet(0);

        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B2', 'JOB N.')
                ->setCellValue('B3', 'FINAL CLIENT:')
                ->setCellValue('B4', 'PLANT TYPE:')
                ->setCellValue('D2', 'COUNTRY:')
                ->setCellValue('D3', 'LOCATION:')
                ->setCellValue('E2', 'CONS. DATE:');

//        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('uploads/demo1.xlsx');
        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        }

    public function nofileAction()
        {
        $request = $this->getRequest();
        $this->view->fileName = $request->getParam('file');
        // action body
        }

    }

//        $highestRow = $sheet->getHighestRow();
//        $highestColumn = $sheet->getHighestColumn();
//
//        for ($row = 1; $row <= $highestRow; $row++) { 
//          $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, 
//                                          null, true, false);

        
        
        
//    public function excelDemoAction()
//        {
//        error_reporting(E_ALL);
//        ini_set('display_errors', TRUE);
//        ini_set('display_startup_errors', TRUE);
//        date_default_timezone_set('Europe/London');
//
//        define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
//
//        /** Include PHPExcel */
////        require_once dirname(__FILE__) . '/../Classes/PHPExcel.php';
//
//        // Create new PHPExcel object
//        echo date('H:i:s') , " Create new PHPExcel object" , EOL;
//        $objPHPExcel = new PHPExcel();
//
//        // Set document properties
//        echo date('H:i:s') , " Set document properties" , EOL;
//        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
//                                                                 ->setLastModifiedBy("Maarten Balliauw")
//                                                                 ->setTitle("PHPExcel Test Document")
//                                                                 ->setSubject("PHPExcel Test Document")
//                                                                 ->setDescription("Test document for PHPExcel, generated using PHP classes.")
//                                                                 ->setKeywords("office PHPExcel php")
//                                                                 ->setCategory("Test result file");
//
//
//        // Add some data
//        echo date('H:i:s') , " Add some data" , EOL;
//        $objPHPExcel->setActiveSheetIndex(0)
//                    ->setCellValue('A1', 'Hello')
//                    ->setCellValue('B2', 'world!')
//                    ->setCellValue('C1', 'Hello')
//                    ->setCellValue('D2', 'world!');
//
//        // Miscellaneous glyphs, UTF-8
//        $objPHPExcel->setActiveSheetIndex(0)
//                    ->setCellValue('A4', 'Miscellaneous glyphs')
//                    ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');
//
//
//        $objPHPExcel->getActiveSheet()->setCellValue('A8',"Hello\nWorld");
//        $objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(-1);
//        $objPHPExcel->getActiveSheet()->getStyle('A8')->getAlignment()->setWrapText(true);
//
//
//        $value = "-ValueA\n-Value B\n-Value C";
//        $objPHPExcel->getActiveSheet()->setCellValue('A10', $value);
//        $objPHPExcel->getActiveSheet()->getRowDimension(10)->setRowHeight(-1);
//        $objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setWrapText(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A10')->setQuotePrefix(true);
//
//
//
//        // Rename worksheet
//        echo date('H:i:s') , " Rename worksheet" , EOL;
//        $objPHPExcel->getActiveSheet()->setTitle('Simple');
//
//
//        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
//        $objPHPExcel->setActiveSheetIndex(0);
//
//
//        // Save Excel 2007 file
//        echo date('H:i:s') , " Write to Excel2007 format" , EOL;
//        $callStartTime = microtime(true);
//
//        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//        $objWriter->save('uploads/demo1.xlsx');
//        $callEndTime = microtime(true);
//        $callTime = $callEndTime - $callStartTime;
//
//        echo date('H:i:s') , " File written to " , 'uploads/demo1.xlsx' , EOL;
//        echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
//        // Echo memory usage
//        echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
//
//
//        // Save Excel 95 file
//        echo date('H:i:s') , " Write to Excel5 format" , EOL;
//        $callStartTime = microtime(true);
//
//        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//        $objWriter->save('uploads/demo1.xls');
//        $callEndTime = microtime(true);
//        $callTime = $callEndTime - $callStartTime;
//
//        echo date('H:i:s') , " File written to " , 'uploads/demo1.xls' , EOL;
//        echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
//        // Echo memory usage
//        echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
//
//
//        // Echo memory peak usage
//        echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;
//
//        // Echo done
//        echo date('H:i:s') , " Done writing files" , EOL;
//        echo 'Files have been created in ' , getcwd() , EOL;
//        }
//
//    }

//\s+<action name="([a-z]+)">\n(.*)\n


