<?php

class statusClass {

    public $qual = true;
    public $sup = true;
    public $pc = true;
    public $cc = false;
    public $my = true;
    public $zero = false;
    public $tot = false;

}

class GiorniLavController extends Zend_Controller_Action {

    private $localData = null;

    public function init() {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
    }

    public function indexAction() {
        // action body
    }

    public function gridAction() {

//        $modifiedTime = "Sat, 26 Jul 1997 05:00:00 GMT";
//        $this->getResponse()
//            ->setHeader('Last-Modified', $modifiedTime, true)
//            ->setHeader('ETag', md5($modifiedTime), true)
//            ->setHeader('Pragma', '', true)
//            ->setHeader('Cache-Control', 'max-age=3600', true);
//            ->setHeader('Expires', $expires, true)

        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currOrder = $request->getParam('order');
        $currTitle = 'Target';
        $this->findProject($currId);
        $arrTitleRev = $this->setProjTitle(' - ' . $currTitle);
        $dataGrid = $this->buildStepArray($currId, $currOrder);
        $keys = array();
        $values = array();

        foreach ($dataGrid->currData as $line) {
            if (array_key_exists('keyRichiesta', $line) && $line['keyRichiesta'] != '') {
                array_push($keys, $line['keyRichiesta']);
            }
        }

        $jsData = "\n";
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/giorni-lav.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jexcel/dist/js/jquery.jexcel.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/heavy-table/javascripts/jquery.heavyTable.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/tableExport.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-double-scroll/jquery.doubleScroll.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jquery.base64.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/html2canvas.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/libs/sprintf.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/jspdf.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/libs/base64.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/tableheadfixer-js/tableHeadFixer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/sticky-table-js/jquery.stickytable.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/tipsy-js/src/javascripts/jquery.tipsy.js');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/jsdebounce/jquery.ba-throttle-debounce.min');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jexcel/dist/css/jquery.jexcel.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/sticky-table-js/jquery.stickytable.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/heavy-table/stylesheets/style.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/heavy-table/stylesheets/normalize.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles_heavy.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/giorni_lav_grid.css');
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/tipsy-js/src/stylesheets/tipsy.css');
        $this->view->dataGrid = $dataGrid;
        $this->view->currId = $currId;
        $this->view->currRel = $dataGrid->projData['Revisione'];
        $this->view->jsGrid = "";

        $currGrid = new Custom_XmlGrid();
        $grid = $currGrid->getGrid($this, 'savetot', $currId);
//        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
//                '/styles/styles.css');
//        $this->view->title = $currGrid->getTitle();
//        $this->view->jsGrid = $currGrid->getJavascript();
        $this->view->revGrid = $grid->deploy();


        if ($dataGrid->Reference != '') {
            $tmpDate = DateTime::createFromFormat('Y-m-d', $dataGrid->Reference);
            $this->view->currReference = date_format($tmpDate, 'd/m/Y');
            $this->view->currRefMonth = date_format($tmpDate, 'm');
            $this->view->currRefYear = date_format($tmpDate, 'Y');
        } else {
            $this->view->currReference = '';
        }

        if ($dataGrid->CashReference != '') {
            $tmpDate = DateTime::createFromFormat('Y-m-d', $dataGrid->CashReference);
            $this->view->currCashReference = date_format($tmpDate, 'd/m/Y');
            $this->view->currCashRefMonth = date_format($tmpDate, 'm');
            $this->view->currCashRefYear = date_format($tmpDate, 'Y');
        } else {
            $this->view->currCashReference = '';
        }

        $tmpStatus = new statusClass();
        if (isset($defaultNamespace->currStatus)) {
            foreach (get_object_vars($defaultNamespace->currStatus) as $key => $value) {
                $tmpStatus->$key = $value;
            }
        }
        $defaultNamespace->currStatus = $tmpStatus;
        $this->view->concurrents = $this->checkConcurrents($defaultNamespace->allowedGrid);
        $this->view->totLabel = $translate->_("Totale corrente");
        $this->view->progLabel = $translate->_("Progressivo corrente");
        $this->view->currStatus = $tmpStatus;
        $this->view->arrTitleRev = $arrTitleRev;
        $this->view->title = $currTitle;
        $this->view->projTitle = $defaultNamespace->cant2;
        $this->view->keys = implode(',', $keys);
        $this->view->gridLen = count($dataGrid->totData['totTarget']);
        $this->view->currOrder = $currOrder;
        $this->view->cantiere = $defaultNamespace->cantiere;
        if (!$this->checkProjAccess($currId, 'Target')) {
            $defaultNamespace->aclReadonly = '1';
        }
        if (!$defaultNamespace->aclReadonly) {
            $concurrents = $this->checkConcurrents($defaultNamespace->allowedGrid);
            if ($concurrents != 'OK' && strpos($concurrents, 'planning') !== false) {
                $this->view->readonly = true;
                $defaultNamespace->titleTest = 'Readonly!';
                $defaultNamespace->aclReadonly = '1';
            } else {
                $this->view->readonly = $defaultNamespace->aclReadonly;
            }
            $this->view->messageConcurrents = $concurrents;
        } else {
            $this->view->messageConcurrents = 'OK';
            $this->view->readonly = $defaultNamespace->aclReadonly;
        }
    }

    public function gridEffAction() {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currOrder = $request->getParam('order');
        $currTitle = 'Actual';
        $this->findProject($currId);
        $this->setProjTitle(' - ' . $currTitle);
        $dataGrid = $this->buildStepArray($currId, $currOrder);
        $keys = array();

        foreach ($dataGrid->currData as $line) {
            if (array_key_exists('keyRichiesta', $line) && $line['keyRichiesta'] != '') {
                array_push($keys, $line['keyRichiesta']);
            }
        }

        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/giorni-lav.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jexcel/dist/js/jquery.jexcel.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/heavy-table/javascripts/jquery.heavyTable.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/tableExport.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jquery.base64.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/html2canvas.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/libs/sprintf.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/jspdf.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/libs/base64.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/tableheadfixer-js/tableHeadFixer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/sticky-table-js/jquery.stickytable.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/tipsy-js/src/javascripts/jquery.tipsy.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jexcel/dist/css/jquery.jexcel.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/sticky-table-js/jquery.stickytable.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/heavy-table/stylesheets/style.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/heavy-table/stylesheets/normalize.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles_heavy.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/giorni_lav_grid.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/tipsy-js/src/stylesheets/tipsy.css');
        $this->view->dataGrid = $dataGrid;
        $this->view->currId = $currId;
        $this->view->jsGrid = "";

        if ($dataGrid->Reference != '') {
            $tmpDate = DateTime::createFromFormat('Y-m-d', $dataGrid->Reference);
            $this->view->currReference = date_format($tmpDate, 'd/m/Y');
        } else {
            $this->view->currReference = '';
        }

        $tmpStatus = new statusClass();
        if (isset($defaultNamespace->currStatus)) {
            foreach (get_object_vars($defaultNamespace->currStatus) as $key => $value) {
                $tmpStatus->$key = $value;
            }
        }

        $this->view->totLabel = $translate->_("Totale corrente");
        $this->view->progLabel = $translate->_("Progressivo corrente");
        $this->view->currStatus = $tmpStatus;
        $this->view->title = $currTitle;
        $this->view->keys = implode(',', $keys);
        $this->view->gridLen = count($dataGrid->totData['totTarget']);
        $this->view->currOrder = $currOrder;
        $this->view->cantiere = $defaultNamespace->cantiere;
        $this->view->concurrents = $this->checkConcurrents($defaultNamespace->allowedGridEff);
        if (!$this->checkProjAccess($currId, 'Actual')) {
            $defaultNamespace->aclReadonly = '1';
        }
        if (!$defaultNamespace->aclReadonly) {
            $concurrents = $this->checkConcurrents($defaultNamespace->allowedGridEff);
            if ($concurrents != 'OK' && strpos($concurrents, 'actual') !== false) {
                $this->view->readonly = true;
                $defaultNamespace->titleTest = 'Readonly!';
                $defaultNamespace->aclReadonly = '1';
            } else {
                $this->view->readonly = $defaultNamespace->aclReadonly;
            }
            $this->view->messageConcurrents = $concurrents;
        } else {
            $this->view->messageConcurrents = 'OK';
            $this->view->readonly = $defaultNamespace->aclReadonly;
        }

        $this->view->readonly = $defaultNamespace->aclReadonly;
    }

    public function gridSpeseAction() {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currOrder = $request->getParam('order');
        $currTitle = 'Cash expenses';
        $this->findProject($currId);
        $this->setProjTitle(' - ' . $currTitle);
        $dataGrid = $this->buildStepArray($currId, $currOrder);
        $keys = array();
        $cashForm = new Custom_XmlForm();
        $cashForm->initForm($this->db, 'detail_cash', 0);
        $cashForm->setCurrAction($defaultNamespace->baseUrl .
                '/spese-mese/edit/id/' . $currId);
        // . '/ym/' . $currYM . '/ric/' . $currRic
        $this->view->cashForm = $cashForm->getForm();

        $poForm = new Custom_XmlForm();
        $poForm->initForm($this->db, 'purchase_orders', 0);
        $poForm->setCurrAction($defaultNamespace->baseUrl .
                '/spese-mese/edit/id/' . $currId);
        // . '/ym/' . $currYM . '/ric/' . $currRic
        $this->view->poForm = $poForm->getForm();


        foreach ($dataGrid->currData as $line) {
            if (array_key_exists('keyRichiesta', $line) && $line['keyRichiesta'] != '') {
                array_push($keys, $line['keyRichiesta']);
            }
        }

        if ($dataGrid->CashReference != '0000-00-00') {
            $tmpDate = DateTime::createFromFormat('Y-m-d', $dataGrid->CashReference);
            $this->view->currCashReference = date_format($tmpDate, 'd/m/Y');
        } else {
            $this->view->currCashReference = '';
        }

        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/js/giorni-lav.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/heavy-table/javascripts/jquery.heavyTable.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/tableExport.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jquery.base64.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/html2canvas.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/libs/sprintf.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/jspdf.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/table-export/jspdf/libs/base64.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/tableheadfixer-js/tableHeadFixer.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/sticky-table-js/jquery.stickytable.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/tipsy-js/src/javascripts/jquery.tipsy.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/sticky-table-js/jquery.stickytable.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/heavy-table/stylesheets/style.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/heavy-table/stylesheets/normalize.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles_heavy.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/giorni_lav_grid.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/tipsy-js/src/stylesheets/tipsy.css');
        $this->view->dataGrid = $dataGrid;
        $this->view->currId = $currId;
        $this->view->jsGrid = "";


        $tmpStatus = new statusClass();
        if (isset($defaultNamespace->currStatus)) {
            foreach (get_object_vars($defaultNamespace->currStatus) as $key => $value) {
                $tmpStatus->$key = $value;
            }
        }

        $this->view->totLabel = $translate->_("Totale corrente");
        $this->view->progLabel = $translate->_("Progressivo corrente");
        $this->view->title = $currTitle;
        $this->view->keys = implode(',', $keys);
        $this->view->gridLen = count($dataGrid->totData['totTarget']);
        $this->view->currStatus = $tmpStatus;
        $this->view->currOrder = $currOrder;
        $this->view->cantiere = $defaultNamespace->cantiere;
        if (!$this->checkProjAccess($currId, 'Cash')) {
            $defaultNamespace->aclReadonly = '1';
        }
        $this->view->readonly = $defaultNamespace->aclReadonly;
    }

    public function savegridAction() {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currMode = $request->getParam('mode');
        $currExit = $request->getParam('esci');
        $currDiscard = $request->getParam('annulla');
        $currGGLav = $request->getParam('gglav');
        $currGGLavTot = $request->getParam('gglavtot');
        $currGGLavAllTot = $request->getParam('gglavalltot');
        $currGGLavTotContr = $request->getParam('gglavtotcontr');
        $currGGLavTotValGrid = $request->getParam('gglavtotvalgrid');
        $currGGLavExcel = $request->getParam('gglavexcel');
        $currDataKeys = $request->getParam('datakeys');
        $currGridLen = $request->getParam('gridlen');
        $currOrder = $request->getParam('orderby');
        $currProject = $request->getParam('currid');
        $currRevision = $request->getParam('currrel');
        $tmpStatus = $request->getParam('status');
        $outMode = $request->getParam('outmode');
        $outAjax = $request->getParam('outajax');
        $outPar = $request->getParam('outpar');
        $outPar2 = $request->getParam('outpar2');
        $dataKeys = explode(',', $currDataKeys);
        $currRefActual = $request->getParam('refactual');
        $currRefCash = $request->getParam('refcash');
        $currRefMonth = $request->getParam('refmonth');
        $currRefYear = $request->getParam('refyear');
        $defaultNamespace->currPlanning = $currProject;
        $GGLavArr = json_decode(stripslashes($currGGLav));
        $GGLavArrTot = json_decode(stripslashes($currGGLavTot));
        $GGLavArrAllTot = json_decode(stripslashes($currGGLavAllTot));
        $GGLavArrTotContr = json_decode(stripslashes($currGGLavTotContr));
        $GGLavValGrid = json_decode(stripslashes($currGGLavTotValGrid));
        $noteTotals = $request->getParam('note-totals');
        $referenceDate = $request->getParam('reference-date');
        $GGToExcel = json_decode(stripslashes($currGGLavExcel));
//        var_dump($GGLavValGrid);
//        die();
        if (!is_object($defaultNamespace->currStatus)) {
            $currStatus = new statusClass();
        } else {
            $currStatus = $defaultNamespace->currStatus;
        }

        $newStatus = json_decode(stripslashes($tmpStatus));
        foreach (get_object_vars($newStatus) as $key => $value) {
            $currStatus->$key = $value;
        }

        $defaultNamespace->currStatus = $currStatus;

        if ($currDiscard == 'annulla') {
            $defaultNamespace->currPlanning = '';
            $this->_redirect('/progetti/list');
        }

        if ($referenceDate != '') {
            $tmpDate = DateTime::createFromFormat('d/m/Y', $referenceDate);
            $sqlDate = date_format($tmpDate, 'Y-m-d');
            if ($currMode == "cash") {
                $sql = 'UPDATE progetti SET CashReference = "' . $sqlDate .
                        '" WHERE idProgetto = "' . $currProject . '"';
            } else {
                $sql = 'UPDATE progetti SET Reference = "' . $sqlDate .
                        '" WHERE idProgetto = "' . $currProject . '"';
            }
            $this->db->query($sql);
        }
        $GGTitles = $GGLavArr[0];
        unset($GGLavArr[0]);
        $keys = array();
        $currKeys = '';
        foreach ($dataKeys as $key => $value) {
            if ($value > 0) {
                if ($currKeys != '') {
                    $currKeys .= ',';
                }
                $currKeys .= $value;
                $keys[] = $value;
            }
        }
        $keyCounter = 0;
        $newGGLav = array();
        if ($GGLavArr != '' && $dataKeys != '') {
            foreach ($GGLavArr as $key => $line) {
                if (count($line) > 1) {
                    for ($y = 5; $y < count($line) - 4; $y++) {
                        $newKey = $dataKeys[$key - 1] . '/' . $GGTitles[$y];
                        $newGGLav[$newKey] = $line[$y];
                    }
                }
            }
            if ($currMode == 'plan') {
                $currModeField = 'GiorniTarget';
            } else {
                $currModeField = 'GiorniEff';
            }

            if ($currKeys != '') {
                $sql = 'SELECT CONCAT(idRichiesta, "/", LPAD(Mese, 2, "0"), "/", Anno) AS oldKey, 
                           idGiorniLav, idRichiesta, Anno, Mese, GiorniTarget, GiorniEff
                        FROM giorni_lav
                        WHERE idRichiesta IN(' . $currKeys . ')';
                $oldGGLav = $this->db->fetchAll($sql);

                // Update old records
                $updSql = '';
                foreach ($oldGGLav as $oldRecord) {
                    if ($oldRecord[$currModeField] != $newGGLav[$oldRecord['oldKey']]) {
                        $updSql .= 'UPDATE giorni_lav SET ' . $currModeField . '="' .
                                ($newGGLav[$oldRecord['oldKey']] == 'X' ?
                                '' : $newGGLav[$oldRecord['oldKey']]) . '" ' .
                                'WHERE idGiorniLav="' . $oldRecord['idGiorniLav'] . '";';
                    }
//                    if ($oldRecord[$currModeField] != $newGGLav[$oldRecord['oldKey']] && $newGGLav[$oldRecord['oldKey']] != 'X')
//                        {
//                        $updSql .= 'UPDATE giorni_lav SET ' . $currModeField . '="' .
//                                $newGGLav[$oldRecord['oldKey']] . '" ' .
//                                'WHERE idGiorniLav="' . $oldRecord['idGiorniLav'] . '";';
//                        }
                    $newGGLav[$oldRecord['oldKey']] = 0;
                }
                if ($updSql != '') {
                    $this->db->query($updSql);
                }

                $insSql = '';
                foreach ($newGGLav as $newKey => $newValue) {
                    if ($newValue != 0 && !array_key_exists($newKey, $oldGGLav)) {
                        $newKeyArr = explode('/', $newKey);
                        $insSql .= 'INSERT INTO giorni_lav  
                                    (idRichiesta, Mese, Anno, ' . $currModeField . ') 
                                    VALUES ("' .
                                $newKeyArr[0] . '","' .
                                $newKeyArr[1] . '","' .
                                $newKeyArr[2] . '","' .
                                $newValue . '");';
                    }
                }
                //        var_dump($updSql, $insSql);    
                if ($insSql != '') {
                    $this->db->query($insSql);
                }

                // Cleanup
                $sql = 'DELETE gl FROM giorni_lav gl ' .
                        'LEFT JOIN dett_giorni_lav dg ON ' .
                        'dg.Anno=gl.Anno AND ' .
                        'dg.Mese=gl.Mese AND ' .
                        'dg.idRichiesta=gl.idRichiesta ' .
                        'WHERE gl.idRichiesta IN(' . $currKeys . ') ' .
                        'AND dg.idDettGGLav IS NULL ' .
                        'AND COALESCE(gl.GiorniTarget, 0) = 0 ' .
                        'AND COALESCE(gl.GiorniEff, 0) = 0 AND ' .
                        '(gl.Note IS NULL OR gl.Note = "")';

//                $sql = 'DELETE FROM giorni_lav WHERE idRichiesta IN(' . $currKeys .
//                        ') AND COALESCE(GiorniTarget, 0) = 0 '.
//                        'AND COALESCE(GiorniEff, 0) = 0 AND '.
//                        '(Note IS NULL OR Note = "")';
                $this->db->query($sql);
            }
        }

        if ($currExit == 'esci') {
            if ($outAjax) {
                exit();
            }
            $defaultNamespace->currPlanning = '';
            $this->_redirect('/progetti/list');
        } else if ($outMode == 'fase') {
            $this->_redirect('/prog-steps/list/id/' . $currProject . '/fase/' . $outPar);
        } else if ($outMode == 'att') {
            $this->_redirect('/richieste/list/id/' . $outPar);
        } else if ($outMode == 'graph-tot') {
            $this->_request->setPost(array(
                'idMultiJobs' => array($currProject),
                'Progressive' => 0));
            $this->_forward('tot-graph', 'reports');
////            $this->_forward('tot-graph', 'reports', null, array('idMultiJob'=>array($currProject), 'Progressive' => 0));
//            $this->_forward('tot-graph', 'dashboard', null, array('id'=>$currProject));
        } else if ($outMode == 'graph-prog') {
            $this->_request->setPost(array(
                'idMultiJobs' => array($currProject),
                'Progressive' => 1));
            $this->_forward('tot-graph', 'reports');
//            $this->_forward('tot-graph', 'reports', null, array('idMultiJob'=>array($currProject), 'Progressive' => '1'));
//            $this->_forward('prog-graph', 'dashboard', null, array('id'=>$currProject));
        } else if ($outMode == 'qual') {
            if ($outPar > 0) {
                $this->findReq($outPar);
                $this->_redirect('/richieste/edit/id/' . $outPar);
            } else {
                $this->findStep($outPar2);
                $this->_redirect('/richieste/new/id/' . $outPar2);
            }
        } else if ($outMode == 'pers') {
            $this->findReq($outPar2);
            if ($outPar > 0) {
                $this->_redirect('/jobs/edit/id/' . $outPar);
            } else {
                $this->_redirect('/jobs/new/id/' . $outPar2);
            }
        } else if ($outMode == 'totals') {
            $sql = 'UPDATE progetti SET ' .
                    'Revisione = ' . ($currRevision + 1) . ',' .
                    'DataRevisione = NOW(), ' .
                    'idUtenteRevisione = ' . $defaultNamespace->user['id_utente'] .
                    ' WHERE idProgetto = ' . $currProject;
            $qry = $this->db->query($sql);

            $newGGLavTot = array();
            $newGGLavTotContr = array();
            for ($y = 1; $y < count($GGLavArrTot) - 4; $y++) {
                $newKey = $GGTitles[$y + 4];
                $newGGLavTot[$newKey] = $GGLavArrTot[$y];
                $newGGLavTotContr[$newKey] = $GGLavArrTotContr[$y];
            }
            $maxTot = count($GGLavArrTot);
            $sql = 'INSERT INTO savetot(id_utente, idProgetto, Reference, Versione, RefVersione, RefCash, TotGG, TotContrGG, TotRate, TotContrRate, TotPM, TotContrPM, Note) VALUES("' .
                    $defaultNamespace->user['id_utente'] . '", "' .
                    $currProject .
                    '", NOW(), "' .
                    ($currRevision + 1) . '", "' .
                    ($currRefActual) . '", "' .
                    ($currRefCash) . '", "' .
                    $GGLavArrTot[$maxTot - 4] . '", "' .
                    $GGLavArrTotContr[$maxTot - 5] . '", "' .
                    $this->normalizeCurrency($GGLavArrTot[$maxTot - 3]) . '", "' .
                    $this->normalizeCurrency($GGLavArrTotContr[$maxTot - 3]) . '", "' .
                    $this->normalizeCurrency($GGLavArrTot[$maxTot - 2]) . '", "' .
                    $this->normalizeCurrency($GGLavArrTotContr[$maxTot - 2]) . '", "' .
                    $noteTotals . '")';
            if (($retVal = $this->db->query($sql)) == false) {
                $ret = mysql_error();
                return;
            } else {
                $idSaveTot = $this->db->lastInsertId();
            }

            $insSql = '';
            $prog = 0;
            $progContr = 0;
            $progRate = 0;
            $progRateContr = 0;
            $progPM = 0;
            $progPMContr = 0;
            $indMonthYear = 0;
            foreach ($newGGLavTot as $newKey => $newValue) {
                $currAllTot = $GGLavArrAllTot[$indMonthYear++];
                $newValueContr = $newGGLavTotContr[$newKey];
                $prog += $newValue;
                $progContr += $newValueContr;
                $progRate += $currAllTot->Rates;
                $progRateContr += $currAllTot->Rates;
                $progPM += $currAllTot->PMs;
                $progPMContr += $currAllTot->NotInvPMs;
                $newKeyArr = explode('/', $newKey);
                $insSql .= 'INSERT INTO savetot_dett
                            (idSaveTot, Mese, Anno, 
                            Totale, Progressivo, 
                            Pers, PersContratto,
                            TotaleContratto, ProgrContratto,
                            Rate, ProgRate,
                            RateContratto, ProgRateContratto,
                            PM, ProgPM,
                            PMContratto, ProgPMContratto,
                            Cash, CashContratto) SELECT "' .
                        $idSaveTot . '","' .
                        $newKeyArr[0] . '","' .
                        $newKeyArr[1] . '","' .
                        $newValue . '","' .
                        $prog . '","' .
                        $currAllTot->Pers . '","' .
                        $currAllTot->NotInvPers . '","' .
                        $newValueContr . '","' .
                        $progContr . '","' .
                        $currAllTot->Rates . '","' .
                        $progRate . '","' .
                        $currAllTot->NotInvRates . '","' .
                        $progRateContr . '","' .
                        $currAllTot->PMs . '","' .
                        $progPM . '","' .
                        $currAllTot->NotInvPMs . '","' .
                        $progPMContr . '", spesemese.Importo, spesemese.Importo ' .
                        'FROM progetti LEFT OUTER JOIN spesemese ON spesemese.idProgetto = "' .
                        $currProject . '" AND spesemese.Anno = "' .
                        $newKeyArr[1] . '" AND spesemese.Mese = "' .
                        $newKeyArr[0] . '" WHERE progetti.idProgetto = "' . $currProject . '";';
            }
            if ($insSql != '') {
//                echo $insSql;
                $this->db->query($insSql);
            }
            if ($outAjax) {
                echo $currRevision;
                exit();
            }
            $this->_redirect('/giorni-lav/grid/id/' . $currProject . '/order/' . $currOrder);
        } else if ($outMode == 'plan') {
            $this->_redirect('/giorni-lav/grid/id/' . $currProject . '/order/' . $currOrder);
        } else if ($outMode == 'riass') {
            $this->_redirect('/dashboard/riassunto/id/' . $currProject);
        } else if ($outMode == 'eff') {
            $this->_redirect('/giorni-lav/grid-eff/id/' . $currProject . '/order/' . $currOrder);
        } else if ($outMode == 'cash') {
            $this->_redirect('/giorni-lav/grid-spese/id/' . $currProject . '/order/' . $currOrder);
        } else if ($currMode == 'plan') {
            if ($outMode == 'excel') {
//                $this->getResponse()->setHeader('Content-type: application/vnd.ms-excel')
                $this->getResponse()->setHeader('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                        ->setHeader('Content-Disposition: attachment; filename="file.xlsx"')
                        ->setHeader('Cache-Control: max-age=0')
                        ->setHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT')
                        ->setHeader('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT')
                        ->setHeader('Cache-Control: cache, must-revalidate')
                        ->setHeader('Pragma: public');
                $this->saveExcel($currProject, $GGToExcel);
            } else {
                $this->_redirect('/giorni-lav/grid/id/' . $currProject . '/order/' . $currOrder);
            }
        } else if ($currMode == 'eff') {
            $this->_redirect('/giorni-lav/grid-eff/id/' . $currProject . '/order/' . $currOrder);
        }
//        
    }

    private function saveExcel($currProject, $GGLavArr) {
        $defaultNamespace = new Zend_Session_Namespace('Default');

//        error_reporting(E_ALL);
//        ini_set('display_errors', TRUE);
//        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Europe/London');

        $inputFileName = 'templates/plan-template.xlsx';

        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' .
                    $e->getMessage());
        }


        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B2', $defaultNamespace->currProject['JobNumber'])
                ->setCellValue('B3', $defaultNamespace->currProject['NomeCliente'])
                ->setCellValue('B4', $defaultNamespace->currProject['Impianto'])
                ->setCellValue('D2', $defaultNamespace->currProject['Paese'])
                ->setCellValue('D3', $defaultNamespace->currProject['Indirizzo'])
                ->setCellValue('E4', $defaultNamespace->currProject['Revisione'])
                ->setCellValue('E2', 'CONS. DATE:');

        $row = 6;
        $sheet = $objPHPExcel->getActiveSheet();
        $styleArray = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'FFFF00')),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => 'AAAAAA')))
        );

        foreach ($GGLavArr as $key => $line) {
            if (count($line) > 1) {
                $col = 0;
                foreach ($line as $index => $value) {
                    $cell = $sheet->getCellByColumnAndRow($col++, $row);
                    $rowIndex = $cell->getRow();
                    $colIndex = $cell->getColumn();
                    $styleArray['fill']['startcolor']['rgb'] = $this->rgbToColor($value->color);
                    $cell->setValue($value->span . " " . $value->value);
                    $cell->getStyle()->applyFromArray($styleArray);
                    if (isset($value->span) && $value->span > 1) {
                        $endCol = PHPExcel_Cell::stringFromColumnIndex($col + $value->span - 2);
                        $sheet->mergeCells($colIndex . $rowIndex . ':' . $endCol . $rowIndex);
                        $col += ($value->span - 1);
                    }
                }
            }
            $row++;
        }
        $objPHPExcel->setActiveSheetIndex(0);

//                'Content-type', 'application/pdf')
//           ->setHeader('Pragma', 'public') // required
//           ->setHeader('Expires', '0')
//           ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
//           ->setHeader('Cache-Control', 'private',false) // required for certain browsers
//           ->setHeader('Content-Type', '$ctype');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
//        $objWriter->save('uploads/demo1.xlsx');
    }

//                    $address = $colIndex.':'.$rowIndex;
//                    if(property_exists($value, 'span'))
//        $sheet = $objPHPExcel->getSheet(0);
//                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++, 
//                            $row, $value->color);
//                    $color = $this->rgbToColor($value->color);
//                    getFill()->
//                    $cell->getStyle()->getFill()
//                                ->setFillType(PHPExcel_Style_Fill::FILL_NONE)
//                                ->getStartColor()->setRGB($color);
//                    $cell->getStyle()->getFill()
//                                ->setFillType(PHPExcel_Style_Fill::FILL_NONE)
//                                ->getStartColor()->setRGB($color);
//                    $objPHPExcel->getActiveSheet()->getStyle($address)->getFill()
//                                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
//                                ->getStartColor()->setARGB('FFE8E5E5');
//                                 ->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
//                                 ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
//                    ->setRGB('FF0000')
//        $objPHPExcel->getActiveSheet()->setTitle('Simple');$value=='X'?'':

    private function rgbToColor($value) {
        $chunks = explode('(', $value);
        $rgb = explode(')', $chunks[1]);
        $listColors = str_replace(' ', '', $rgb[0]);
        $rgbArr = explode(',', $listColors);
        $retVal = sprintf('%02X%02X%02X', $rgbArr[0], $rgbArr[1], $rgbArr[2]);
        return $retVal;
    }

    private function normalizeCurrency($value) {
        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }

    private function stripslashes_deep($value) {
        $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

        return $value;
    }

    public function effettiviAction() {
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
        if ($addLink != '') {
            $this->view->addLink = $this->view->baseUrl() . $addLink;
            $this->view->addLabel = $currGrid->getLinkTitle();
        }
//                    
    }

    private function MyGrid($title = '') {
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

        $grid->setParam('deploy.pdf.title', $title)
                ->setParam('deploy.pdf.subtitle', '')
                ->setParam('deploy.print.title', $title);
        return $grid;
    }

    public function getDetailAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currRicR = $request->getParam('ric');
        $currAnnoR = $request->getParam('anno');
        $currMeseR = $request->getParam('mese');
        $currStatusR = $request->getParam('status');
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            $currRic = $formData['ric'];
            $currAnno = $formData['anno'];
            $currMese = $formData['mese'];
            $currStatus = $formData['status'];
        }
        $sql = sprintf('SELECT * FROM dett_giorni_lav WHERE idRichiesta = "%d" AND Anno ="%d" AND Mese ="%d"',
                ($currRic + 0), ($currAnno + 0), ($currMese + 0));
//        $sql = sprintf('SELECT * FROM dett_giorni_lav WHERE idRichiesta = "%d" AND Anno ="%d" AND Mese ="%d" AND Status ="%s"',
//                ($currRic+0), ($currAnno+0), ($currMese+0), $currStatus); 
        $dettData = $this->db->fetchAll($sql);
        $retData = array();
        $maxDays = cal_days_in_month(CAL_GREGORIAN, ($currMese + 0), ($currAnno + 0));
        $date = new DateTime(sprintf('%04d-%02d-01', $currAnno, $currMese));
        if (count($dettData) == 0) {
            for ($i = 1; $i <= $maxDays; $i++) {
                $retData[] = array($date->format('D'), 0, 0, '', '');
                $date->modify('+1 day');
            }
        } else {
            foreach ($dettData as $cd) {
                $currData = $cd;
                if ($cd['Status'] == 'A')
                    break;
            }
//            $currData=$dettData[0];
            for ($i = 1; $i <= $maxDays; $i++) {
                $GG = sprintf('%02d', $i);
                $retData[] = array($date->format('D'), $currData['OreGG_' . $GG], $currData['FattGG_' . $GG], $currData['TipoGG_' . $GG], $currData['NoteGG_' . $GG]);
                $date->modify('+1 day');
            }
        }
        echo json_encode($retData);
        exit;
//                    
    }

    public function saveDetailAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currRic = $request->getParam('ric');
        $currAnno = $request->getParam('anno');
        $currMese = $request->getParam('mese');
        $currDett = $request->getParam('dett');
        $currMode = $request->getParam('mode');
        $currStatus = $request->getParam('status');
        $flagNew = $request->getParam('dett-new');
        if ($flagNew == 'new') {
            $delNewSql = 'DELETE FROM giorni_lav WHERE ' .
                    'idRichiesta ="' . $currRic . '" AND ' .
                    'Anno = "' . $currAnno . '" AND ' .
                    'Mese = "' . $currMese . '"';
            $this->db->query($delNewSql);

            $newSql = 'INSERT INTO giorni_lav (idRichiesta, Anno, Mese, Note) VALUES (' .
                    '"' . $currRic . '", "' . $currAnno . '", "' . $currMese . '", "")';
            $this->db->query($newSql);
        }

        $dettArr = explode(',', $currDett);
        $delSql = 'DELETE FROM dett_giorni_lav WHERE idRichiesta = "' . $currRic .
                '" AND Anno ="' . $currAnno . '" AND Mese ="' . $currMese . '"';
        $this->db->query($delSql);

        $maxDays = count($dettArr) / 5;
        $insSql = 'INSERT INTO dett_giorni_lav (idRichiesta, Anno, Mese, Status';
        for ($i = 1; $i <= $maxDays; $i++) {
            $insSql .= sprintf(', OreGG_%02d, FattGG_%02d, TipoGG_%02d, NoteGG_%02d', $i, $i, $i, $i);
        }
        $insSql .= ', TotGGLav, TotGGNR';
        $totDays = 0;
        $totNR = 0;
        $totOW = 0;
        if ($currMode == 'keep') {
            $insSql .= ') VALUES ("' .
                    $currRic . '","' .
                    $currAnno . '","' .
                    $currMese . '","' .
                    $currStatus . '"';
            for ($i = 1; $i <= $maxDays; $i++) {
                $base = ($i - 1) * 5;
                $insSql .= ', "' . $dettArr[$base + 1] . '", "' . $dettArr[$base + 2] .
                        '", "' . $dettArr[$base + 3] . '", "' . $dettArr[$base + 4] . '"';
                switch ($dettArr[$base + 2]) {
                    case 'P':  //Present
                    case 'N':  //Not recognized
                    case 'S':  //Smart Working
                        $totDays++;
                        break;

                    case 'O':
                    case 'W':
                        $totOW++;
                        break;

                    case 'X':
                        $totNR++;
                        break;

                    default:
                        break;
                }
//                if($dettArr[$base+2] == 'P' || $dettArr[$base+2] == 'N')
//                    {
//                    $totDays++;
//                    }
//                else if($dettArr[$base+2] == 'X')
//                    {
//                    $totDays++;
//                    }
            }
            $insSql .= ', ' . $totDays . ', ' . $totNR . ')';
            $this->db->query($insSql);
        }
//        if($currMode == 'keep' || $currMode == 'keep')
        if ($currMode == 'keep') {
            $updSql = 'UPDATE giorni_lav SET ';
            if ($currStatus == 'T') {
                $updSql .= 'GiorniTarget = ';
            } else {
                $updSql .= 'GiorniEff = ';
            }
            $updSql .= '"' . $totDays . '" WHERE ' .
                    'idRichiesta ="' . $currRic . '" AND ' .
                    'Anno = "' . $currAnno . '" AND ' .
                    'Mese = "' . $currMese . '"';
            $this->db->query($updSql);
        }
        echo json_encode(array("gg" => $totDays, "nr" => $totNR, "ow" => $totOW));
//                    
    }

    public function saveNoteAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        $flagNew = $request->getParam('out-note-new');
        $currRic = $request->getParam('out-note-ric');
        $currAnno = $request->getParam('out-note-year');
        $currMese = $request->getParam('out-note-month');
        $currNote = $request->getParam('note-cell');
        if ($flagNew == 'new') {
            $sql = 'INSERT INTO giorni_lav (idRichiesta, Anno, Mese, Note) VALUES (' .
                    '"' . $currRic . '", "' . $currAnno . '", "' . $currMese . '", "' . $currNote . '")';
        } else {
            $sql = 'UPDATE giorni_lav SET Note = "' . $currNote .
                    '" WHERE idRichiesta = "' . $currRic .
                    '" AND Anno ="' . $currAnno .
                    '" AND Mese ="' . $currMese . '"';
        }
        $this->db->query($sql);
    }

    public function dispDettRevAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = 'SELECT * FROM savetot_dett ' .
                'WHERE idSaveTot = "' . $currId . '" AND ' .
                'Anno <> 0 AND Mese <> 0 ' .
                'ORDER BY Anno, Mese';
        $revDetail = $this->db->fetchAll($sql);
        $outDett = '<table><tbody>';

        $currAnno = $revDetail[0]['Anno'];
        $mountCount = 0;
        $outDett .= '<tr class="sticky-row"><td>Year</td>';
        foreach ($revDetail as $detailLine) {
            if ($currAnno != $detailLine['Anno']) {
                $outDett .= '<td colspan=' . $mountCount . '>' . $currAnno . '</td>';
                $currAnno = $detailLine['Anno'];
                $mountCount = 1;
            } else {
                $mountCount++;
            }
        }
        if ($mountCount > 0) {
            $outDett .= '<td colspan=' . $mountCount . '>' . $currAnno . '</td>';
        }
        $outDett .= '</tr>';

        $outDett .= '<tr class="sticky-row"><td>Month</td>';
        foreach ($revDetail as $detailLine) {
            $outDett .= '<td>' . $detailLine['Mese'] . '</td>';
//            $outDett .= '<td>'.$detailLine['Anno'].'/'.
//                    $detailLine['Mese'].'</td>';
        }
        $outDett .= '</tr>';
        $outDett .= '<tr class="gridColTotals"><td class="sticky-cell">Contract Total</td>';
        foreach ($revDetail as $detailLine) {
            $outDett .= '<td>' . $detailLine['TotaleContratto'] . '</td>';
        }
        $outDett .= '</tr>';
        $outDett .= '<tr class="gridColProgressive"><td class="sticky-cell">Contract Progressive</td>';
        foreach ($revDetail as $detailLine) {
            $outDett .= '<td>' . $detailLine['ProgrContratto'] . '</td>';
        }
        $outDett .= '</tr>';
        $outDett .= '<tr class="gridColTotals2"><td class="sticky-cell">Total</td>';
        foreach ($revDetail as $detailLine) {
            $outDett .= '<td>' . $detailLine['Totale'] . '</td>';
        }
        $outDett .= '</tr>';
        $outDett .= '<tr class="gridColProgressive2"><td class="sticky-cell">Progressive</td>';
        foreach ($revDetail as $detailLine) {
            $outDett .= '<td>' . $detailLine['Progressivo'] . '</td>';
        }
        $outDett .= '</tr>';
        $outDett .= '</tbody></table>';

//        if($flagNew == 'new')
//            {
//            $sql = 'INSERT INTO giorni_lav (idRichiesta, Anno, Mese, Note) VALUES ('.
//                    '"'.$currRic.'", "'.$currAnno.'", "'.$currMese.'", "'.$currNote.'")';
//            }
//        else
//            {
//            $sql = 'UPDATE giorni_lav SET Note = "'.$currNote.
//                '" WHERE idRichiesta = "'.$currRic.
//                '" AND Anno ="'.$currAnno.
//                '" AND Mese ="'.$currMese.'"';
//            }
//        $this->db->query($sql);
        echo $outDett;
    }

//    public function listRatesAction() {
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
//        $request = $this->getRequest();
//        if ($this->_request->isPost()) {
//            $formData = $this->_request->getPost();
//            $currId = $formData['id'];
//            $currYM = $formData['ym'];
//            $currRic = $formData['ric'];
//            $currEdit = $formData['edit'];
//            $annoMese = explode('-', $currYM);
//            echo $this->findCashTitle($currId, $currRic);
//            echo 'Date : '.$annoMese[0].'/'.$annoMese[1].'<br>';    
//            echo $this->findCashDetail($currId, $currRic, $annoMese[1], $annoMese[0]);
//
//        }
//    }
//
//    private function findCashTitle($currId, $currRic) {
//        $sql = "SELECT 
//            progetti.Nome AS Commessa,
//            progetti.JobNumber,
//            prog_steps.Step, 
//            qualifiche.Descrizione AS Qualifica,
//            forn_pers.RagSoc,
//            CONCAT(personale.Nome, ' ', Cognome) AS Pers
//        FROM
//            richieste
//        INNER JOIN
//            prog_steps
//        ON
//            prog_steps.idProgStep = richieste.idProgStep
//        INNER JOIN
//            progetti
//        ON
//            progetti.idProgetto = prog_steps.idProgetto
//        LEFT JOIN
//            qualifiche
//        ON
//            qualifiche.idQualifica = richieste.idQualifica
//        LEFT JOIN
//            jobs
//        ON
//            jobs.idRichiesta = richieste.idRichiesta
//        LEFT JOIN
//            personale
//        ON
//            personale.idPersonale = jobs.idPersonale
//        LEFT JOIN
//            forn_pers
//        ON
//            forn_pers.idFornPers = personale.idFornPers
//        WHERE
//            progetti.idProgetto = '".$currId."' AND 
//            richieste.idRichiesta = '".$currRic."'";
//
//        $result = $this->db->fetchRow($sql);
//        $retVal = 'Job : '.$result['Commessa'].' ('.$result['JobNumber'].')<br>'.
//                  'Activity - Qual. : '.$result['Step'].' - '.$result['Qualifica'].'<br>'.
//                  'Supervisor : ' . $result['Pers'] . '<br>' .
//                  'Company : ' . $result['RagSoc'] . '<br>';
//        return $retVal;
//        }
//
//    private function findCashDetail($currId, $currRic, $anno, $mese) {
//        $sql = "SELECT 
//                spesemese.idSpeseMese,
//                spesemese.Importo AS Amount,
//                spesemese.Storno AS Balance,
//                spesemese.Fattura AS Invoice,
//                spesemese.Ordine AS 'P.O.',
//                CONCAT(
//                    tab_tipospesa.Descrizione,
//                    IF(
//                        tab_tipospesa.Descrizione = 'Others',
//                        CONCAT(' (', spesemese.DescTipoSpesa, ')'),
//                        ''
//                    )
//                ) AS 'Cost Type'
//            FROM
//                spesemese
//            LEFT JOIN
//                tab_tipospesa
//            ON
//                tab_tipospesa.idTabTipoSpesa = spesemese.idTipoSpesa
//            INNER JOIN
//                progetti
//            ON
//                progetti.idProgetto = spesemese.idProgetto
//            LEFT JOIN
//                `richieste`
//            ON
//                richieste.idRichiesta = spesemese.idRichiesta
//            WHERE
//                spesemese.idProgetto = '" . $currId . "' AND 
//                spesemese.Anno = '" . $anno . "' AND 
//                spesemese.Mese = '" . $mese . "' AND
//                spesemese.idRichiesta = '" . $currRic . "'";
//
//        $result = $this->db->fetchAll($sql);
//        $retVal = '<table id="cashDetTable" align="top" cellspacing="0" cellpadding="0">'.
//                '<thead><tr>';
//        $arrTitles = $result[0];
//        array_shift($arrTitles);
//        foreach ($arrTitles as $key => $value) {
//            $retVal .= '<th>'.$key.'</th>'; 
//        }
//        $retVal .= '</tr><tbody><tr id="cdRow-0" class="gridEvid"><td colspan='.
//                        count($result[0]).'>New</td></tr>';
//        foreach ($result as $currLine) {
//            $currId = array_shift($currLine);
//            $retVal .= '<tr id="cdRow-'.$currId.'">';
//            foreach ($currLine as $key => $value) {
//                $retVal .= '<td>'.$value.'</td>'; 
//                
//            }
//            $retVal .= '</tr>';
//        }
//        $retVal .= '</tbody></table>';
//        return $retVal;
//    }
//    
//    public function getCashRowAction() {
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
//        $request = $this->getRequest();
//        if ($this->_request->isPost()) {
//            $formData = $this->_request->getPost();
//            $currId = $formData['id'];
//            $sql = "SELECT ".
//                    "spesemese.idSpeseMese, ".
//                    "spesemese.idTipoSpesa, ".
//                    "spesemese.DescTipoSpesa, ".
//                    "spesemese.Importo, ".
//                    "spesemese.Storno, ".
//                    "spesemese.Fattura, ".
//                    "spesemese.Ordine ".
//                 "FROM ".
//                    "spesemese ".
//                 "WHERE ".
//                    "spesemese.idSpeseMese = '" . $currId . "'";
//            $result = $this->db->fetchRow($sql);
//            echo json_encode($result);    
//        }
//    }
//
//    public function saveCashRowAction() {
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
//        $request = $this->getRequest();
//        if ($this->_request->isPost()) {
//            $formData = $this->_request->getPost();
//            $currId = $formData['idSpeseMese'];
//            if($currId > 0)
//                {
//                $updSql = 'UPDATE spesemese SET '.
//                    'spesemese.idTipoSpesa = '.    $formData['idTipoSpesa'].', '.
//                    'spesemese.DescTipoSpesa = "'. $formData['DescTipoSpesa'].'", '.
//                    'spesemese.Importo = '.        $formData['Importo'].', '.
//                    'spesemese.Storno = '.         $formData['Storno'].', '.
//                    'spesemese.Fattura = "'.       $formData['Fattura'].'", '.
//                    'spesemese.Ordine = "'.        $formData['Ordine'].'" '.
//                    'WHERE spesemese.idSpeseMese = '.$currId;    
//                $this->db->query($updSql);
//                }
//            else 
//                {
//                $insSql = 'INSERT INTO spesemese ('.
//                    'idProgetto, '.   
//                    'idRichiesta, '.   
//                    'Anno, '.   
//                    'Mese, '.   
//                    'idTipoSpesa, '.
//                    'DescTipoSpesa, '.
//                    'Importo, '.
//                    'Storno, '.
//                    'Fattura, '.
//                    'Ordine ) VALUES ('.
//                         $formData['idProgetto'].', '.
//                         $formData['idRichiesta'].', '.
//                    '"'. $formData['Anno'].'", '.
//                    '"'. $formData['Mese'].'", '.
//                         $formData['idTipoSpesa'].', '.
//                    '"'. $formData['DescTipoSpesa'].'", '.
//                         $formData['Importo'].', '.
//                         $formData['Storno'].', '.
//                    '"'. $formData['Fattura'].'", '.
//                    '"'. $formData['Ordine'].'") ';    
//                $this->db->query($insSql);
//                }    
//            }
//        }
//
//    public function delCashRowAction() {
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
//        $request = $this->getRequest();
//        if ($this->_request->isPost()) {
//            $formData = $this->_request->getPost();
//            $currId = $formData['toDel'];
//            if($currId > 0)
//                {
//                $delSql = 'DELETE spesemese FROM spesemese '.
//                    'WHERE spesemese.idSpeseMese = '.$currId;    
//                $this->db->query($delSql);
//                }
//            }
//        }
//
        private function checkProjAccess($currId, $mode)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $currUser = $defaultNamespace->user;
        $currUserLevel = $defaultNamespace->area;
        $allowed = array('Admin', 'SuperIT', 'SuperLU');

        if(in_array($currUserLevel, $allowed))
        {
        return true;
        }
//        if($currUserLevel != 'Project' && $currUserLevel != 'PM-CM')
//            {
//            return true;
//            }
//        else if($mode != 'Target')
//            {
//            return false;
//            }
        $sql = 'SELECT 
            progetti.Nome
          FROM
            utenti
          INNER JOIN
            tab_userlevel ON tab_userlevel.id_userlevel = utenti.id_userlevel
          LEFT JOIN
            autorizz_progetti ON autorizz_progetti.id_utente = utenti.id_utente
          INNER JOIN
            tab_guests ON tab_guests.Tablename = "autorizz_progetti"
          INNER JOIN
            tab_hosts ON tab_hosts.Tablename = "progetti"
          LEFT JOIN
            guesthost ON guesthost.idTab_guest = tab_guests.idTab_guest AND 
                  guesthost.idTab_host = tab_hosts.idTab_host AND 
                  guesthost.idGuest = autorizz_progetti.idAutorizzProgetto
          LEFT JOIN
            progetti ON progetti.idProgetto = guesthost.idHost
          WHERE
            (
              (tab_userlevel.desc_ul_short LIKE "Project" OR 
                tab_userlevel.desc_ul_short LIKE "PM-CM")   AND
              utenti.id_utente = "' . $currUser['id_utente'] . '" AND
              progetti.idProgetto = "' . $currId . '"  
            )';
        $result = $this->db->fetchAll($sql);
        return (count($result) >= 1);
    }

    private function checkConcurrents($allowedList) {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $currPlan = $defaultNamespace->currProject['JobNumber'];
        $sql = 'SELECT activity.*, utenti.nome, utenti.cognome, tab_userlevel.Titolo FROM activity ' .
                'JOIN utenti ON utenti.id_utente = activity.id_utente ' .
                'JOIN tab_userlevel ON tab_userlevel.id_userlevel = utenti.id_userlevel WHERE ' .
                'activity.Action LIKE "%' . $currPlan . '" AND ' .
                'tab_userlevel.desc_ul_short IN (' . $allowedList . ') AND ' .
                'TIMESTAMPDIFF(SECOND, LastPing, NOW()) < 20 AND ' .
                'activity.id_utente <> "' . $defaultNamespace->user['id_utente'] . '"';
        $result = $this->db->fetchAll($sql);
        if (count($result) >= 1) {
            $currAct = "planning";
            foreach ($result as $rec) {
                if (substr($rec['Action'], 0, 6) == "Actual") {
                    $currAct = "working (actual)";
                }
            }
            if (count($result) == 1) {
                $message = 'Another user is ' . $currAct . ' on this job!<ul>';
            } else {
                $message = count($result) . ' users are ' . $currAct . ' on this job!';
            }
            foreach ($result as $currUser) {
                $message .= '<li> ' . $currUser['nome'] .
                        " " . $currUser['cognome'] .
                        " - " . $currUser['Titolo'] . '</li';
            }
            $message .= '</ul>';
        } else {
            $message = 'OK';
        }
        return $message;
    }

    public function pingPlanAction() {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currMode = $request->getParam('mode');
        $currAction = $request->getParam('act');
        $timeNow = new Zend_Date();
        $lastPing = $defaultNamespace->lastActivityPing;
        $retVal = 'OK';
        if (isset($lastPing)) {
            $difference = $timeNow->sub($lastPing);
            $measure = new Zend_Measure_Time($difference->toValue(), Zend_Measure_Time::SECOND);
        } else {
            $measure = 31;
        }

        if ($currMode == 'start' || $measure > 30) {
            if ($currAction == 'Eff') {
                $act = "Actual";
                $allowed = $defaultNamespace->allowedGridEff;
            } else {
                $act = "Plan";
                $allowed = $defaultNamespace->allowedGrid;
            }
            $currPlan = $act . ' ' . $defaultNamespace->currProject['JobNumber'];
            $insSql = 'INSERT INTO activity (Action, id_utente, LastPing) ' .
                    'VALUES ("' . $currPlan . '", "' . $defaultNamespace->user['id_utente'] . '", NOW())';
            $this->db->query($insSql);
            $currActivity = $this->db->lastInsertId();
            $defaultNamespace->currActivity = $currActivity;
            $reval = $this->checkConcurrents($allowed);
        } else {
            $currActivity = $defaultNamespace->currActivity;
            $updSql = 'UPDATE activity SET LastPing = NOW() WHERE idActivity = "' . $currActivity . '"';
            $this->db->query($updSql);
//            return 'OK';
        }
        $defaultNamespace->lastActivityPing = $timeNow;
        echo $retVal;
//        echo $this->checkConcurrents();
        session_write_close();
        exit;
    }

    private function buildStepArray($currId, $orderBy) {
        $translate = Zend_Registry::get('translate');
        $order = 'aree.Ordine, aree.Vendor, aree.NotInvoiced, aree.Area, ';

//  OLD        
//        switch ($orderBy)
//            {
//            case 1:     // "Qualification"
//                $order .= 'qualifiche.Descrizione, richieste.idRichiesta, glav.Anno, glav.Mese';
//                break;
//            case 2:     // "Supervisor"
//                $order .= 'CONCAT(pers.Cognome, " ", pers.Nome), richieste.idRichiesta, glav.Anno, glav.Mese';
//                break;
//            case 3:     // "Personnel Company"
//                $order .= 'forn_pers.RagSoc, richieste.idRichiesta, glav.Anno, glav.Mese';
//                break;
//            case 4:     // "Ordine attività"
//                $order .= 'prog_steps.Ordine, CONCAT(pers.Cognome, " ", pers.Nome), richieste.idRichiesta, glav.Anno, glav.Mese';
//                break;
//            default:
//                $order .= 'aree.Area, richieste.idRichiesta, glav.Anno, glav.Mese';
//                break;
//            }
        switch ($orderBy) {
            case 1:     // "Qualification"
                $order .= 'qualifiche.Descrizione,';
                break;
            case 2:     // "Supervisor"
                $order .= 'CONCAT(pers.Cognome, " ", pers.Nome),';
                break;
            case 3:     // "Personnel Company"
                $order .= 'fp1.RagSoc,';
                break;
            case 4:     // "Ordine attività"
                $order .= 'CONCAT(pers.Cognome, " ", pers.Nome),';
                break;
            default:
//                $order .= 'aree.Area';
                break;
        }
        $order .= 'prog_steps.Ordine, richieste.Ordine, richieste.idRichiesta, glav.Anno, glav.Mese';

        $sql = 'SELECT DISTINCT aree.idArea, ' .
                'aree.Area, ' .
                'aree.AreaShort, ' .
                'aree.Vendor, ' .
                'aree.NotInvoiced, ' .
                'progetti.Nome AS nomeCommessa, ' .
                'progetti.JobNumber AS jobNumber, ' .
                'progetti.Impianto AS Impianto, ' .
                'progetti.Paese AS Paese, ' .
                'progetti.Indirizzo AS Indirizzo, ' .
                'progetti.PocketMoney AS basePM, ' .
                'progetti.ODFcosts, ' .
                'progetti.Inizio, ' .
                'progetti.Fine, ' .
                'progetti.Reference, ' .
                'progetti.Revisione, ' .
                'progetti.DataRevisione, ' .
                'progetti.idUtenteRevisione, ' .
                'progetti.ContractMD, ' .
                'MONTH(progetti.Reference) AS MeseRef, ' .
                'YEAR(progetti.Reference) AS AnnoRef, ' .
                'progetti.CashReference, ' .
                'prog_steps.Step AS Step, ' .
                'prog_steps.idProgStep AS idProgStep, ' .
                'richieste.Ordine AS ordRichiesta, ' .
                'richieste.idRichiesta AS keyRichiesta, ' .
                'qualifiche.Descrizione AS Qualifica, ' .
                'qualifiche.tarOrariaBase AS baseRateHH, ' .
                'qualifiche.tarGgBase AS baseRateGG, ' .
                'CONCAT(pers.Nome, " ", pers.Cognome) AS NomeCognome, ' .
                'pers.idPersonale AS idPers, ' .
                'pers.Nome AS Nome, ' .
                'pers.Cognome AS Cognome, ' .
                'fp1.idFornPers AS idFp1, ' .
                'fp1.RagSoc AS RagSoc, ' .
                'fp1.Nome AS FornPers, ' .
                'fp2.idFornPers AS idFp2, ' .
                'fp2.RagSoc AS CurrRagSoc, ' .
                'fp2.Nome AS CurrFornPers, ' .
                'jobs.idJob, ' .
                'jobs.salGG AS RateGG, ' .
                'jobs.salOrario AS RateHH, ' .
                'jobs.PocketMoney AS PocketMoney, ' .
                'pfp1.RateGG AS Pers1RateGG, ' .
                'pfp1.RateHH AS Pers1RateHH, ' .
                'pfp1.PocketMoney AS Pers1PocketMoney, ' .
                'pfp2.RateGG AS Pers2RateGG, ' .
                'pfp2.RateHH AS Pers2RateHH, ' .
                'pfp2.PocketMoney AS Pers2PocketMoney, ' .
                'IF(contratti3.idContratto IS NULL, contratti2.Contratto, contratti3.Contratto) AS Contratto, ' .
                'IF(contratti3.idContratto IS NULL, contratti2.Sigla, contratti3.Sigla) AS Sigla, ' .
                '(contratti3.idContratto IS NULL OR contratti3.idContratto = contratti2.idContratto) AS MainContrOwned, ' .
                'glav.Mese, ' .
                'glav.Anno, ' .
                'CONCAT(LPAD(glav.Mese, 2, "0"), "/", glav.Anno) AS GGLavKey, ' .
                'IF(glav.GiorniEff > 0, glav.GiorniEff, glav.GiorniTarget) AS GiorniTarget, ' .
                'glav.GiorniEff AS GiorniEff, ' .
                'glav.FerieEff AS FerieEff, ' .
                'glav.PermEff AS PermEff, ' .
                'glav.MalEff AS MalEff, ' .
                'glav.Note AS NoteGG, ' .
                'dett_giorni_lav.idDettGGLav AS HasDetail, ' .
                'dett_giorni_lav.Status AS StatusDetail, ' .
                'dett_giorni_lav.TotGGNR, ' .
                'altriProgetti.gl2Planned, ' .
                'altriProgetti.gl2Actual, ' .
                'altriProgetti.gl2Total, ' .
                'altriProgetti.pr2Progetti ' .
                'FROM progetti ' .
                'LEFT JOIN aree ON aree.idProgetto = progetti.idProgetto ' .
                'LEFT JOIN prog_steps ON prog_steps.idProgetto = aree.idProgetto AND prog_steps.idArea = aree.idArea ' .
                'LEFT JOIN richieste ON richieste.idProgStep = prog_steps.idProgStep ' .
                'LEFT JOIN jobs ON jobs.idRichiesta = richieste.idRichiesta ' .
                'LEFT JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica ' .
                'LEFT JOIN personale pers ON pers.idPersonale = jobs.idPersonale ' .
                'LEFT JOIN contratti AS contratti3 ON contratti3.idContratto = aree.idContratto ' .
                'LEFT JOIN contratti AS contratti1 ON contratti1.idContratto = richieste.idContratto ' .
                'LEFT JOIN contratti AS contratti2 ON contratti2.idContratto = progetti.PWMainContractor ' .
                'LEFT JOIN giorni_lav glav ON glav.idRichiesta = richieste.idRichiesta ' .
                'LEFT JOIN pers_forn_pers AS pfp1 ON pfp1.idPersonale = jobs.idPersonale AND ' .
                'pfp1.idProgetto = progetti.idProgetto AND ' .
                'DATE(CONCAT(glav.Anno, "-", glav.Mese, "-15")) BETWEEN pfp1.Dal AND pfp1.Al ' .
                'LEFT JOIN forn_pers AS fp1 ON fp1.idFornPers = pfp1.idFornPers ' .
                'LEFT JOIN forn_pers AS fp2 ON fp2.idFornPers = pers.idFornPers ' .
                'LEFT JOIN pers_forn_pers AS pfp2 ON pfp2.idPersonale = jobs.idPersonale AND ' .
                'pfp2.idProgetto = "0" AND ' .
                'DATE(CONCAT(glav.Anno, "-", glav.Mese, "-15")) BETWEEN pfp2.Dal AND pfp2.Al ' .
                'LEFT JOIN dett_giorni_lav ON dett_giorni_lav.idRichiesta = richieste.idRichiesta AND ' .
                'dett_giorni_lav.Anno = glav.Anno AND ' .
                'dett_giorni_lav.Mese = glav.Mese  ' .
                'LEFT JOIN ' .
                '(SELECT ' .
                'SUM(IF(gl2.GiorniEff > 0, gl2.GiorniEff, gl2.GiorniTarget)) AS gl2Total,' .
                'SUM(gl2.GiorniTarget) AS gl2Planned,' .
                'SUM(gl2.GiorniEff) AS gl2Actual,' .
                'jb2.idPersonale AS jb2idPersonale,' .
                'gl2.Anno AS gl2Anno,' .
                'gl2.Mese AS gl2Mese,' .
                'ps2.idProgetto AS ps2idProgetto, ' .
                'CONCAT("<br><table><tr><td>Job</td><td>Prog.</td><td>Act.</td></tr>",' .
                'GROUP_CONCAT("<tr><td>",CONCAT(COALESCE(pr2.Nome,""), ' .
                '" ", COALESCE(pr2.jobNumber,""), ' .
                '"</td><td>", COALESCE(gl2.GiorniTarget,""), ' .
                '"</td><td>", COALESCE(gl2.GiorniEff,""), "</tr>")),' .
                '"</table>") AS pr2Progetti ' .
                'FROM ' .
                'giorni_lav AS gl2 ' .
                'JOIN ' .
                'richieste AS rq2 ON rq2.idRichiesta = gl2.idRichiesta ' .
                'JOIN ' .
                'jobs AS jb2 ON jb2.idRichiesta = gl2.idRichiesta ' .
                'JOIN ' .
                'prog_steps AS ps2 ON ps2.idProgStep = rq2.idProgStep ' .
                'JOIN ' .
                'progetti AS pr2 ON pr2.idProgetto = ps2.idProgetto ' .
                'WHERE ps2.idProgetto <> "' . $currId . '" ' .
                'GROUP BY ' .
                'jb2.idPersonale, ' .
                'gl2.Anno, ' .
                'gl2.Mese ' .
                ') altriProgetti ' .
                'ON jb2idPersonale = pers.idPersonale AND ' .
                'gl2Anno = glav.Anno AND gl2Mese = glav.Mese ' .
                'WHERE progetti.idProgetto = "' . $currId . '" ' .
                'ORDER BY ' . $order;

//                    '(SELECT SUM(gl2.GiorniTarget) AS "gl2Planned", '.
//                        'SUM(gl2.GiorniEff) AS "gl2Actual", '.
//                        'rq2.idPersonale AS gl2idPersonale, '.
//                        'gl2.Anno AS gl2Anno, '.
//                        'gl2.Mese AS gl2Mese, '.
//                        'ps2.idProgetto AS gl2idProgetto '.
//                    'FROM giorni_lav AS gl2 '.
//                    'JOIN richieste AS rq2 ON rq2.idRichiesta = gl2.idRichiesta '.
//                    'JOIN prog_steps AS ps2 ON ps2.idProgStep = rq2.idProgStep '.
//                    'GROUP BY rq2.idPersonale, gl2.Anno, gl2.Mese) altriProgetti '.
//                    'ON gl2idPersonale = personale.idPersonale AND '.
//                        'gl2Anno = giorni_lav.Anno AND '.
//                        'gl2Mese = giorni_lav.Mese AND '.
//                        'gl2idProgetto <> "' . $currId . '" '.
//                'giorni_lav.GiorniTarget AS GiorniTarget, ' .
//                       'CASE WHEN contratti1.idContratto IS NULL AND contratti3.idContratto IS NULL '.  
//                'THEN contratti2.Contratto '.
//                'ELSE CONCAT(' - ',
//                                   personale.Nome, ' ',
//                                   personale.Cognome, ' (',
//                                   forn_pers.RagSoc, ')')
//                            END ) AS Value 
//                'LEFT JOIN pers_forn_pers AS pers_forn_pers1 ON pers_forn_pers.idProgetto = progetti.idProgetto AND '.
//                '     pers_forn_pers.idPersonale = personale.idPersonale AND ' .
//                '     CONCAT(CAST(giorni_lav.Anno AS CHAR), "-", CAST(giorni_lav.Mese AS CHAR), "-01") ' .
//                '     BETWEEN pers_forn_pers.Dal AND pers_forn_pers.Al ' .
//                'LEFT JOIN pers_forn_pers AS pers_forn_pers2 ON pers_forn_pers.idPersonale = personale.idPersonale AND ' .
//                '     CONCAT(CAST(giorni_lav.Anno AS CHAR), "-", CAST(giorni_lav.Mese AS CHAR), "-01") ' .
//                '     BETWEEN pers_forn_pers.Dal AND pers_forn_pers.Al ' .

        $allAtt = $this->db->fetchAll($sql);
        $currAtt = $allAtt[0];
        $projArr = array(
            'nomeCommessa' => $currAtt['nomeCommessa'],
            'contractMD' => $currAtt['ContractMD'],
            'jobNumber' => $currAtt['jobNumber'],
            'Impianto' => $currAtt['Impianto'],
            'Paese' => $currAtt['Paese'],
            'Indirizzo' => $currAtt['Indirizzo'],
            'basePM' => $currAtt['basePM'],
            'ODFcosts' => $currAtt['ODFcosts'],
            'Inizio' => $currAtt['Inizio'],
            'Fine' => $currAtt['Fine'],
            'Revisione' => $currAtt['Revisione'],
            'Reference' => $currAtt['Reference'],
            'MeseRef' => $currAtt['MeseRef'],
            'AnnoRef' => $currAtt['AnnoRef'],
            'CashReference' => $currAtt['CashReference']
        );
        $start = new DateTime($currAtt['Inizio']);
        $start->modify('first day of this month');
//        $start = (new DateTime($currAtt['Inizio']))->modify('first day of this month');
        $end = new DateTime($currAtt['Fine']);
        $end->modify('first day of next month');
//        $end = (new DateTime($currAtt['Fine']))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);

        $currTotTarLine = array('Qualifica' => $translate->_("Totale corrente"),
            'NomeCognome' => '',
            'idProgStep' => '',
            'keyRichiesta' => '');
        $currTotInvLine = array('Qualifica' => $translate->_("Totale da fatt."),
            'NomeCognome' => '',
            'idProgStep' => '',
            'keyRichiesta' => '');
        $currTotEffLine = array('Qualifica' => $translate->_('Tot. effettivo'),
            'NomeCognome' => '',
            'idProgStep' => '',
            'keyRichiesta' => '');
        $currProgTarLine = array('Qualifica' => $translate->_('Progressivo corrente'),
            'NomeCognome' => '',
            'idProgStep' => '',
            'keyRichiesta' => '');
        $currProgInvLine = array('Qualifica' => $translate->_('Progr. da fatt.'),
            'NomeCognome' => '',
            'idProgStep' => '',
            'keyRichiesta' => '');
        $currProgEffLine = array('Qualifica' => $translate->_('Progr. effettivo'),
            'NomeCognome' => '',
            'idProgStep' => '',
            'keyRichiesta' => '');
        $currTotSpeseLine = array('Qualifica' => $translate->_('Tot. spese'),
            'NomeCognome' => '',
            'idProgStep' => '',
            'keyRichiesta' => '');
        $currProgSpeseLine = array('Qualifica' => $translate->_('Progressivo'),
            'NomeCognome' => '',
            'idProgStep' => '',
            'keyRichiesta' => '');
        foreach ($period as $dt) {
            $currProgTarLine[$dt->format("m/Y")] = 0;
            $currProgInvLine[$dt->format("m/Y")] = 0;
            $currTotTarLine[$dt->format("m/Y")] = 0;
            $currTotInvLine[$dt->format("m/Y")] = 0;
            $currProgEffLine[$dt->format("m/Y")] = 0;
            $currTotEffLine[$dt->format("m/Y")] = 0;
            $currProgSpeseLine[$dt->format("m/Y")] = 0;
            $currTotSpeseLine[$dt->format("m/Y")] = 0;
        }
        $retArr = array();
        $totArr = array();
        $currIdReq = '-1';
        $currIdArea = '-1';
        $currIdProgStep = '-1';
        $currModeField = 'GiorniTarget';
        foreach ($allAtt as $att) {
            if ($currIdReq != $att['keyRichiesta'] ||
                    $currIdArea != $att['idArea'] ||
                    $currIdProgStep != $att['idProgStep']) {
                $currIdReq = $att['keyRichiesta'];
                $currIdArea = $att['idArea'];
                $currIdProgStep = $att['idProgStep'];
//                $currArrId = 'id-' . $currIdArea . '-' . $currIdReq;
                $currArrId = 'id-' . $currIdArea . '-' . $currIdReq . '-' . $currIdProgStep;
//                $currRate = $att['RateGG'] > 0 ? $att['RateGG'] : $att['baseRateGG'];
                if (($att['Pers1RateGG'] + 0) > 0) {
                    $currHasRate = true;
                } else {
                    $currHasRate = false;
                }
                $currRate = $this->arrCoalesce(array($att['RateGG'],
                    $att['Pers1RateGG'],
                    $att['Pers2RateGG'],
                    $att['baseRateGG']));
                $currRate += 0;
//                $currPM = $att['PocketMoney'] > 0 ? $att['PocketMoney'] : $att['basePM'];
                $currPM = $this->arrCoalesce(array($att['PocketMoney'],
                    $att['Pers1PocketMoney'],
                    $att['Pers2PocketMoney'],
                    $att['basePM']));

                $currAttLine = array('Qualifica' => $att['Qualifica'],
                    'idPers' => $att['idPers'],
                    'idFp1' => $att['idFp1'],
                    'idFp2' => $att['idFp2'],
                    'Supervisor' => $att['NomeCognome'],
                    'Nome' => $att['Nome'],
                    'Cognome' => $att['Cognome'],
                    'RagSoc' => $att['RagSoc'],
                    'CurrRagSoc' => $att['CurrRagSoc'],
                    'Contratto' => $att['Contratto'],
                    'IsMainContr' => $att['MainContrOwned'],
                    'Sigla' => $att['Sigla'],
                    'idProgStep' => $att['idProgStep'],
                    'Step' => $att['Step'],
                    'FornPers' => $att['FornPers'],
                    'CurrFornPers' => $att['CurrFornPers'],
                    'keyRichiesta' => $att['keyRichiesta'],
                    'idProgStep' => $att['idProgStep'],
                    'idJob' => $att['idJob'],
                    'Activity' => $att['Step'],
                    'keyArea' => $att['idArea'],
                    'Area' => $att['Area'],
                    'AreaShort' => $att['AreaShort'],
                    'Vendor' => $att['Vendor'],
                    'NotInvoiced' => $att['NotInvoiced'],
                    'CurrRate' => $currRate,
                    'CurrPM' => $currPM,
                    'TotGG' => 0,
                    'TotGGEff' => 0,
                    'TotRate' => 0,
                    'TotPM' => 0,
                    'Data' => array(),
                    'ListFornPers' => array($att['CurrFornPers'] => true));
                foreach ($period as $dt) {
                    $maxDays = $dt->format("t");
                    $currAttLine[$dt->format("m/Y")] = '';
                    $currAttLine['Data'][$dt->format("m/Y")] = array(
                        'GiorniTarget' => '',
                        'GiorniTargetAltriProg' => '',
                        'RateTarget' => 0,
                        'RateGG' => $currRate,
                        'RatePM' => $currPM,
                        'PMTarget' => $maxDays * $currPM,
                        'GiorniNotInv' => 0,
                        'NoteGG' => '',
                        'HasDetail' => '',
                        'StatusDetail' => '',
                        'GiorniEff' => '',
                        'GiorniEffAltriProg' => '',
                        'GiorniTotAltriProg' => '',
                        'HasRate' => false,
                        'RateEff' => 0,
                        'PMEff' => 0,
                        'FerieEff' => 0,
                        'PermEff' => 0,
                        'MalEff' => 0);
//                        'currHasRate' => true,    
                }
                if ($att[$currModeField] != '') {
                    $currAttLine[$att['GGLavKey']] = number_format($att[$currModeField]);
                    if ($att['NotInvoiced'] == 0) {
                        $currTotInvLine[$att['GGLavKey']] += $att['GiorniTarget'];
                    }
                    $currTotTarLine[$att['GGLavKey']] += $att['GiorniTarget'];
                    $currTotEffLine[$att['GGLavKey']] += $att['GiorniEff'];
                }
                $currListaForn = $att['FornPers'];
                $retArr[$currArrId] = $currAttLine;
            } else if ($currIdReq != '') {
                $retArr[$currArrId][$att['GGLavKey']] = number_format($att[$currModeField]);

                if (($att['Pers1RateGG'] + 0) > 0) {
                    $currHasRate = true;
                } else {
                    $currHasRate = false;
                }
                $currRate = $this->arrCoalesce(array($att['RateGG'],
                    $att['Pers1RateGG'],
                    $att['Pers2RateGG'],
                    $att['baseRateGG']));
                $currRate += 0;
                $currPM = $this->arrCoalesce(array($att['PocketMoney'],
                    $att['Pers1PocketMoney'],
                    $att['Pers2PocketMoney'],
                    $att['basePM']));
                $currPM += 0;
                if (is_array($retArr[$currArrId][$att['GGLavKey']])) {
                    if (array_key_exists('RateGG', $retArr[$currArrId][$att['GGLavKey']])) {
                        $retArr[$currArrId][$att['GGLavKey']]['RateGG'] = $currRate;
                    }
                    if (array_key_exists('RatePM', $retArr[$currArrId][$att['GGLavKey']])) {
                        $retArr[$currArrId][$att['GGLavKey']]['RatePM'] = $currPM;
                    }
                }

                $currTotTarLine[$att['GGLavKey']] += $att['GiorniTarget'];
                if ($att['NotInvoiced'] == 0) {
                    $currTotInvLine[$att['GGLavKey']] += $att['GiorniTarget'];
                }
                $currTotEffLine[$att['GGLavKey']] += $att['GiorniEff'];
            }
            $totRateTarget = 0;
            $totRateEff = 0;
            $totPMTarget = 0;
            $totPMEff = 0;
            if ($att['FornPers'] != '' &&
                    !array_key_exists($att['FornPers'], $retArr[$currArrId]['ListFornPers'])) {
                $retArr[$currArrId]['ListFornPers'][$att['FornPers']] = false;
            }
            if ($att['Anno'] > 0 && $att['Mese'] > 0) {
                $currGiorniTarget = 0;
                $giorniMese = cal_days_in_month(CAL_GREGORIAN, $att['Mese'], $att['Anno']);
                if (($att['Anno'] > $att['AnnoRef']) || (($att['Anno'] == $att['AnnoRef']) && ($att['Mese'] >= $att['MeseRef']))) {
                    $currGiorniTarget = $att['GiorniTarget'];
                } else {
                    $currGiorniTarget = $att['GiorniEff'];
                }
                if ($att['GiorniTarget'] > 0) {
                    $totRateTarget = $currGiorniTarget * $currRate;
                    if ($currGiorniTarget > 0) {
                        $totPMTarget = $currGiorniTarget * $currPM;
                    }
                }
                if ($att['GiorniEff'] > 0) {
                    $totRateEff = $att['GiorniEff'] * $currRate;
                    $totPMTEff = $att['GiorniEff'] * $currPM;
                }
                if (($att['Pers1RateGG'] + 0) > 0) {
                    $currHasRate = true;
                } else {
                    $currHasRate = false;
                }
                $retArr[$currArrId]['Data'][$att['GGLavKey']] = array(
                    'GiorniTarget' => number_format($att['GiorniTarget']),
                    'GiorniTotAltriProg' => number_format($att['gl2Total']),
                    'GiorniTargetAltriProg' => number_format($att['gl2Planned']),
                    'GiorniTargetListAltriProg' => $att['pr2Progetti'],
                    'HasRate' => $currHasRate,
                    'RateGG' => $currRate,
                    'RatePM' => $currPM,
                    'RateTarget' => $totRateTarget,
                    'PMTarget' => $totPMTarget,
                    'GiorniEff' => $att['GiorniEff'],
                    'GiorniEffAltriProg' => $att['gl2Actual'],
                    'RateEff' => $totRateEff,
                    'PMEff' => $totPMEff,
                    'GiorniNotInv' => $att['TotGGNR'] + 0,
                    'NoteGG' => $att['NoteGG'],
                    'HasDetail' => ($att['HasDetail'] > 0),
                    'StatusDetail' => $att['StatusDetail'],
                    'FerieEff' => number_format($att['FerieEff']),
                    'PermEff' => number_format($att['PermEff']),
                    'MalEff' => number_format($att['MalEff']));
//                    'GiorniEff' => number_format($att['GiorniEff']),
                $retArr[$currArrId]['TotGGEff'] += $att['GiorniEff'];
                $retArr[$currArrId]['TotGG'] += $currGiorniTarget;
                $retArr[$currArrId]['TotRate'] += $totRateTarget;
                $retArr[$currArrId]['TotPM'] += $totPMTarget;
            }
        }
        $retArr = $this->getRates($currId, $retArr, $start, $end);
        $totArr['totTarget'] = $currTotTarLine;
        $totArr['totInvTarget'] = $currTotInvLine;
        $totArr['totEff'] = $currTotEffLine;
        $totArr['totSpese'] = $currTotSpeseLine;
        $currProgTar = 0;
        $currProgInv = 0;
        $currProgEff = 0;
        $currProgSpese = 0;
        foreach ($period as $dt) {
            $currProgTar += $currTotTarLine[$dt->format("m/Y")];
            $currProgInv += $currTotInvLine[$dt->format("m/Y")];
            $currProgEff += $currTotEffLine[$dt->format("m/Y")];
            $currProgSpese += $currTotSpeseLine[$dt->format("m/Y")];
            $currProgTarLine[$dt->format("m/Y")] = $currProgTar;
            $currProgInvLine[$dt->format("m/Y")] = $currProgInv;
            $currProgEffLine[$dt->format("m/Y")] = $currProgEff;
            $currProgSpeseLine[$dt->format("m/Y")] = $currProgSpese;
        }
        $totArr['progressivoInvTarget'] = $currProgInvLine;
        $totArr['progressivoTarget'] = $currProgTarLine;
        $totArr['progressivoEff'] = $currProgEffLine;
        $totArr['progressivoSpese'] = $currProgSpeseLine;

        $retObj = new stdClass();
        $retObj->projData = $projArr;
        $retObj->currData = $retArr;
        $retObj->totData = $totArr;
        $retObj->eventData = $this->buildEvArray($currId, $start->format('Y-m-d'));
        $retObj->speseData = $this->buildSpeseArray($currId);
        $retObj->Reference = $currAtt['Reference'];
        $retObj->CashReference = $currAtt['CashReference'];
        $retObj->Contract = $this->getContract();
        $retObj->DayTypes = $this->getDayTypes();
        return $retObj;
    }

    private function arrCoalesce($currArr) {
        foreach ($currArr as $currValue) {
            if (($currValue + 0) > 0) {
                return $currValue + 0;
            }
        }
        return 0;
    }

    private function getContract() {
        $sql = 'SELECT '
                . 'tab_fatt_gg.SiglaOpzione AS id,'
                . 'tab_fatt_gg.Opzione AS name '
                . 'FROM tab_fatt_gg '
                . 'ORDER BY tab_fatt_gg.Opzione';
        $allContracts = $this->db->fetchAll($sql);
        array_unshift($allContracts, array('id' => '', 'name' => ''));
        return $allContracts;
    }

    private function getDayTypes() {
        $sql = 'SELECT '
                . 'tab_tipo_gg.SiglaOpzione AS id,'
                . 'tab_tipo_gg.Opzione AS name '
                . 'FROM tab_tipo_gg '
                . 'ORDER BY tab_tipo_gg.Opzione';
        $allTypes = $this->db->fetchAll($sql);
        array_unshift($allTypes, array('id' => '', 'name' => ''));
        return $allTypes;
    }

    private function getRates($currId, $retArr, $startJob, $endJob) {
        $sql = 'SELECT '
                . 'CONCAT("id-", prog_steps.idArea, "-",'
                . '       richieste.idRichiesta) AS arrKey,'
                . 'jobs.Inizio AS jobStart,'
                . 'jobs.Fine AS jobEnd,'
                . 'jobs.SalGG AS jobGG,'
                . 'jobs.PocketMoney AS jobPM,'
                . 'progetti.Inizio AS projStart, '
                . 'progetti.Fine AS projEnd, '
                . 'pers_forn_pers.idProgetto AS rateIdProj,'
                . 'pers_forn_pers.Dal AS rateStart,'
                . 'pers_forn_pers.Al AS rateEnd,'
                . 'pers_forn_pers.RateGG AS rateGG,'
                . 'pers_forn_pers.PocketMoney AS ratePM, '
                . 'CASE WHEN jobs.Inizio > progetti.Inizio THEN jobs.Inizio '
                . '     ELSE progetti.Inizio END AS periodStart, '
                . 'CASE WHEN jobs.Fine < progetti.Fine THEN jobs.Fine '
                . '     ELSE progetti.Fine END AS periodEnd '
                . 'FROM jobs '
                . 'JOIN richieste ON richieste.idRichiesta = jobs.idRichiesta '
                . 'JOIN prog_steps ON prog_steps.idProgStep = richieste.idProgStep '
                . 'JOIN pers_forn_pers ON pers_forn_pers.idPersonale = jobs.idPersonale '
                . 'JOIN progetti ON progetti.idProgetto = prog_steps.idProgetto '
                . 'WHERE progetti.idProgetto = "' . $currId
                . '" AND (pers_forn_pers.idProgetto=0 OR pers_forn_pers.idProgetto IS NULL OR '
                . 'pers_forn_pers.idProgetto=progetti.idProgetto) AND '
                . '(pers_forn_pers.Dal BETWEEN progetti.Inizio AND progetti.fine OR '
                . 'pers_forn_pers.Al BETWEEN progetti.Inizio AND progetti.fine OR '
                . 'progetti.inizio BETWEEN pers_forn_pers.Dal AND pers_forn_pers.Al) '
                . 'ORDER BY pers_forn_pers.idProgetto';
        $allRates = $this->db->fetchAll($sql);
        $interval = DateInterval::createFromDateString('1 month');
        foreach ($allRates as $currRate) {
            $start = new DateTime($currRate['rateStart']);
            $start->modify('first day of this month');
            if ($start < $startJob) {
                $start = $startJob;
            }
            $end = new DateTime($currRate['rateEnd']);
            $end->modify('first day of next month');
            if ($end > $endJob) {
                $end = $endJob;
            }
            $period = new DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $maxDays = $dt->format("t");
                $dateKey = $dt->format("m/Y");
                $retArr[$currRate['arrKey']][$dateKey] = '';
                $retArr[$currRate['arrKey']]['Data'][$dateKey]['RateGG'] = $currRate['rateGG'];
                $retArr[$currRate['arrKey']]['Data'][$dateKey]['RatePM'] = $currRate['ratePM'];
//                $currAttLine['Data'][$dt->format("m/Y")]['RateGG'] = $currRate['jobGG'];
//                $currAttLine['Data'][$dt->format("m/Y")]['RatePM'] = $currRate['jobPM'];
            }
        }
        return $retArr;
    }

    private function buildEvArray($currId, $start) {
        $evSql = 'SELECT eventi.idEvento, ' .
                'eventi.Data, ' .
                'MONTH(eventi.Data) AS Mese, ' .
                'YEAR(eventi.Data) AS Anno, ' .
                'TIMESTAMPDIFF(MONTH, "' . $start . '", eventi.Data) AS MesiOffset, ' .
                'eventi.Evento, ' .
                'eventi.Note, ' .
                'CONCAT(LPAD(MONTH(eventi.Data), 2, "0"), "-", YEAR(eventi.Data)) AS evGGYY ' .
                'FROM eventi ' .
                'WHERE eventi.idProgetto = "' . $currId . '" ' .
                'ORDER BY YEAR(eventi.Data), MONTH(eventi.Data)';
        $allEv = $this->db->fetchAll($evSql);
        foreach ($allEv as $key => $line) {
            $allEv[$key]['Evento'] = $this->cleanEv($line['Evento']);
            $allEv[$key]['Note'] = $this->cleanEv($line['Note']);
        }
        return $allEv;
    }

    private function cleanEv($ev) {
        return str_replace(array("\r\n", "\r", "\n", "\\r\\n", "\\r", "\\n"), '', nl2br($ev, false));
    }

    private function buildSpeseArray($currId) {
        $sql = 'SELECT ' .
                'spesemese.Mese, ' .
                'GROUP_CONCAT(DISTINCT spesemese.idRichiesta) AS idRic, ' .
                'SUM(spesemese.Importo) AS totImp, ' .
                'SUM(spesemese.Storno) AS totStorno, ' .
                'aree.idArea, ' .
                'spesemese.idRichiesta, ' .
                'prog_steps.idProgStep, ' .
                'CONCAT("id-", aree.idArea, "-", spesemese.idRichiesta, "-", prog_steps.idProgStep) AS keyRic, ' .
                'CONCAT(LPAD(spesemese.Mese, 2, "0"), "/", spesemese.Anno) AS GGYY, ' .
                'GROUP_CONCAT(tab_tipospesa.Descrizione) AS tipiSpesa ' .
                'FROM spesemese ' .
                'LEFT JOIN richieste ON richieste.idRichiesta = spesemese.idRichiesta ' .
                'LEFT JOIN prog_steps ON prog_steps.idProgStep = richieste.idProgStep ' .
                'LEFT JOIN aree ON aree.idArea = prog_steps.idArea ' .
                'LEFT JOIN tab_tipospesa ON tab_tipospesa.idTabTipoSpesa=spesemese.idTipoSpesa ' .
                'WHERE spesemese.idProgetto = "' . $currId . '" ' .
                'GROUP BY CONCAT(spesemese.idRichiesta, "-", spesemese.Anno, "-", spesemese.Mese)';



//        'LEFT JOIN progetti ON progetti.idProgetto = spesemese.idProgetto ' .
//        'LEFT JOIN contratti AS contratti3 ON contratti3.idContratto = aree.idContratto ' .
//        'LEFT JOIN contratti AS contratti1 ON contratti1.idContratto = richieste.idContratto ' .
//        'LEFT JOIN contratti AS contratti2 ON contratti2.idContratto = progetti.PWMainContractor ' .
//        'IF(contratti3.idContratto IS NULL, contratti2.Contratto, contratti3.Contratto) AS Contratto, ' .
//            'IF(contratti3.idContratto IS NULL, contratti2.Sigla, contratti3.Sigla) AS Sigla, ' .
//            '(contratti3.idContratto IS NULL OR contratti3.idContratto = contratti2.idContratto) AS MainContrOwned, ' .



        $allAtt = $this->db->fetchAll($sql);
//        'LEFT JOIN dett_spesemese ON dett_spesemese.idSpeseMese = dett_spesemese.idSpeseMese ' . 
//        $allAtt = '';
        if (!$allAtt)
            return array();
        $currAtt = $allAtt[0];
        $spese = array();
        foreach ($allAtt as $riga) {
            $keyRic = 'id-' . $riga['idArea'] . '-' . $riga['idRichiesta'] . '-' . $riga['idProgStep'];
            if (!array_key_exists($keyRic, $spese)) {
                $spese[$keyRic] = array();
                $spese[$keyRic]['detail'] = array();
                $spese[$keyRic]['spesamese'] = 0.0;
            }
            if (!array_key_exists($riga['GGYY'], $spese[$keyRic]['detail'])) {
                $spese[$keyRic]['detail'][$riga['GGYY']] = 0.0;
            }
            $floatSpesa = floatval($riga['totImp']) - floatval($riga['totStorno']);
            $spese[$keyRic]['detail'][$riga['GGYY']] += $floatSpesa;
            $spese[$keyRic]['spesamese'] += $floatSpesa;
        }
        $spese['data'] = $allAtt;
        return $spese;
    }

    private function findProject($currId) {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '') {
            $sql = 'SELECT progetti.*, clienti.Nome AS NomeCliente, ' .
                    'contratti.Sigla AS ContractCompany, tab_contr_md.Opzione FROM progetti ' .
                    'LEFT JOIN clienti ON clienti.idCliente = progetti.idClienteFin ' .
                    'LEFT JOIN contratti ON contratti.idContratto = progetti.PWMainContractor ' .
                    'LEFT JOIN tab_contr_md ON tab_contr_md.IdTabContrMd = progetti.idTabContrMd ' .
                    'WHERE progetti.idProgetto = "' . $currId . '"';
            $currProject = $this->db->fetchRow($sql);
            $defaultNamespace->currProject = $currProject;
        }
//        
    }

    private function setProjTitle($mode) {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        if ($defaultNamespace->currProject['Revisione'] == 0) {
            $currRev = ' - No Rev.';
        } else {
            $currRev = ' - Rev.' . (($defaultNamespace->currProject['Revisione']) - 1);
        }
        $newTitle = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['JobNumber'] . " (" .
                $defaultNamespace->currProject['Nome'] . ") " .
                $defaultNamespace->currProject['ContractCompany'] . " " .
                $defaultNamespace->currProject['Opzione'] . " " .
                $mode;
        $defaultNamespace->cant2 = $newTitle . $currRev;
        return array('Title' => $newTitle, 'Rev' => $currRev);
    }

    private function findReq($currId) {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currReq = '';
        if ($currId <> '') {
            $sql = 'SELECT richieste.*, CONCAT(raggruppamenti.Raggruppamento, " ", 
                            Descrizione) AS Req
                        FROM richieste 
                        LEFT JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica
                        LEFT JOIN raggruppamenti ON raggruppamenti.NumRagg = qualifiche.NumRagg
                    WHERE idRichiesta = "' . $currId . '"';
            $currReq = $this->db->fetchRow($sql);
            $this->findStep($currReq['idProgStep']);
            $defaultNamespace->currReq = $currReq;
        }
//        
    }

    private function findStep($currId) {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currStep = '';
        if ($currId <> '') {
            $sql = 'SELECT prog_steps.*, aree.Area, aree.AreaShort FROM prog_steps 
                    JOIN aree ON aree.idArea = prog_steps.idArea
                    WHERE idProgStep = "' . $currId . '"';
            $currStep = $this->db->fetchRow($sql);
            $this->findProject($currStep['idProgetto']);
            $defaultNamespace->currStep = $currStep;
        }
//        
    }

    private function setStepTitle($title) {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = "Commessa " . $defaultNamespace->currProject['Nome'] .
                " : Attività " . $defaultNamespace->currStep['Step'] . $title;
    }

}
