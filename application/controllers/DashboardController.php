<?php

class DashboardController extends Zend_Controller_Action
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

    public function riassuntoAction()
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $this->setTitle();

        $currTab = $request->getParam('seltab');
        if ($currTab == '')
            {
            $currTab = 0;
            }
        $defaultNamespace->currEdit = $currId;
        $this->view->currTab = $currTab;

        $projForm1 = new Custom_XmlForm();
        $projForm1->initForm($this->db, 'progetto', $currId);
        $projForm1->setCurrAction($defaultNamespace->baseUrl . '/dashboard/save-proj1-dash/id/' . $currId);
        $this->view->javascript1 = $projForm1->getJavascript();

        $projForm2 = new Custom_XmlForm();
        $projForm2->initForm($this->db, 'progetto2', $currId);
        $projForm2->setCurrAction($defaultNamespace->baseUrl . '/dashboard/save-proj2-dash/id/' . $currId);
        $this->view->javascript1b = $projForm2->getJavascript();

        $currGrid = new Custom_XmlGrid();
        $projGrid = $currGrid->getGrid($this, 'prog_steps', $currId);
        $this->view->javascript2 = $currGrid->getJavascript();
        $addLink = $currGrid->getLink();

        $currGrid = new Custom_XmlGrid();
        $contrGrid = $currGrid->getGrid($this, 'pw_contracts', $currId);
        $contrGrid->updateColumn('Contractor',array(
            'searchType' => 'sqlExp',
            'searchSqlExp' => 'Contractor LIKE CONCAT("%",{{value}},"%")'));
//        $persGrid = $currGrid->getGrid($this, 'riass_pers', $currId);
        $this->view->javascript3 = $currGrid->getJavascript();

        $form1 = $projForm1->getForm();
        $form2 = $projForm2->getForm();
        $this->view->form1 = $form1;
        $this->view->form2 = $form2;
        $this->view->grid1 = $projGrid->deploy();
        $this->view->grid2 = $contrGrid->deploy();
        $this->view->contrAddLink = $this->view->baseUrl() . $currGrid->getLink();
        $this->view->contrAddLabel = $currGrid->getLinkTitle();
//        $this->view->grid2 = $persGrid->deploy();
        $this->view->riassArr = $this->getRiass($currId);
        //$riassArr;

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
                '/styles/personale_riass.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/assets/prettify.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.filter.css');
    }

    private function getRiass($currId)
    {
        $sql = 'SELECT
                DATE_FORMAT(progetti.Inizio, "%d/%m/%Y") AS DataInizio,
                DATE_FORMAT(progetti.Fine, "%d/%m/%Y") AS DataFine,
                DATEDIFF(progetti.Fine, progetti.Inizio) AS numGiorni,
                COUNT(richieste.idRichiesta) AS numPers,
                progetti.Budget
                FROM progetti
                LEFT JOIN prog_steps ON prog_steps.idProgetto = progetti.idProgetto
                LEFT JOIN richieste ON richieste.idProgStep = prog_steps.idProgStep
                LEFT JOIN tab_schemi_lav ON tab_schemi_lav.idTabSchemiLav= richieste.idTabSchemiLav
                WHERE progetti.idProgetto = "' . $currId . '"
                GROUP BY progetti.idProgetto';
        $currProject = $this->db->fetchRow($sql);

        $sqlDett = 'SELECT
                  DATE_FORMAT(progetti.Inizio, "%d/%m/%Y") AS DataInizio,
                  DATE_FORMAT(progetti.Fine, "%d/%m/%Y") AS DataFine,
                  DATEDIFF(progetti.Fine, progetti.Inizio) AS ggProg,
                  progetti.Budget,
                  prog_steps.idProgStep,
                  DATE_FORMAT(prog_steps.Inizio, "%d/%m/%Y") AS DataInizioStep,
                  DATE_FORMAT(prog_steps.Fine, "%d/%m/%Y") AS DataFineStep,
                  DATEDIFF(prog_steps.Fine, prog_steps.Inizio) AS ggProgStep,
                  (DATEDIFF(prog_steps.Fine, prog_steps.Inizio) * tab_schemi_lav.ggSett / 7) AS giorniLav,
                  (DATEDIFF(prog_steps.Fine, prog_steps.Inizio) * tab_schemi_lav.oreSett / 7) AS oreLav,
                  (DATEDIFF(prog_steps.Fine, prog_steps.Inizio) * tab_schemi_lav.ggSett / 7) * qualifiche.tarGgBase AS Salari,
                  richieste.idRichiesta
                  FROM progetti
                  LEFT JOIN prog_steps ON prog_steps.idProgetto = progetti.idProgetto
                  LEFT JOIN richieste ON richieste.idProgStep = prog_steps.idProgStep
                  LEFT JOIN tab_schemi_lav ON tab_schemi_lav.idTabSchemiLav= richieste.idTabSchemiLav
                  LEFT JOIN qualifiche ON qualifiche.idQualifica = richieste.idQualifica
                  WHERE progetti.idProgetto = "' . $currId . '"';
        $currProjDett = $this->db->fetchAll($sqlDett);

        $ggLav = 0;
        $totSal = 0;
        $oreLav = 0;
        foreach ($currProjDett as $ricLine)
            {
            $ggLav += $ricLine['giorniLav'];
            $oreLav += $ricLine['oreLav'];
            $totSal += $ricLine['Salari'];
            }

        $riassArr = array(
            'Dal' => $currProject['DataInizio'],
            'Al' => $currProject['DataFine'],
            'Giorni' => $currProject['numGiorni'],
            'Persone' => $currProject['numPers'],
            'GG. Lavorati' => floor($ggLav),
            'Ore Lavorate' => floor($oreLav),
            'Budget' => $currProject['Budget'],
            'Totale salari preventivo' => number_format((float) $totSal, 2, '.', ''),
            'Anticipi salario' => '',
            'Spese' => '',
            'Totale bilancio' => ''
        );
        return $riassArr;
    }

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
//
    }

    private function setTitle()
    {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['Nome'] . " (" .
                $defaultNamespace->currProject['JobNumber'] . ")";
//
    }

    private function setGraphTitle($action)
    {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = $translate->_("Commessa") . " " .
                $defaultNamespace->currProject['Nome'] . " (" .
                $defaultNamespace->currProject['JobNumber'] . ") - " . $action;
//
    }

    public function ganttAction()
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');

//            'class' => 'important',
//            'class' => 'urgent',

        $sql = 'SELECT idProgetto, ' .
                'CONCAT("<a href=\"' . $defaultNamespace->baseUrl .
                '/prog-steps/gantt/id/", idProgetto, ' .
                '"\">", Nome, "</A>") AS label, Inizio AS start, ' .
                'Fine as end, "" AS class FROM progetti';
        $data = $this->db->fetchAll($sql);

        setlocale(LC_ALL, 'it_IT');
        $gantti = new Custom_Gantti($data, array(
            'title' => 'Progetti',
            'cellwidth' => 25,
            'cellheight' => 35,
            'today' => true
        ));

        $this->view->gantti = $gantti;
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/gantti/styles/css/screen.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/gantti/styles/css/gantti.css');
    }

    public function printAction()
    {
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $myPdf = new Custom_Print();
        $mpdf = $myPdf->getMpdf();
        $config = Zend_Registry::get('config');
        $localPath = $config->downloads->get('localPath');
        $css = file_get_contents($localPath . '/../css/pdf.css');
        $mpdf->WriteHTML($css, 1);
        $css = file_get_contents($localPath . '/../css/printjs.css');
        $mpdf->WriteHTML($css, 1);
//        $footherTitle = 'Report di evento';
//        $mpdf->SetFooter('{DATE j-m-Y}|' . $footherTitle . '|{PAGENO}');
        $mpdf->AddPage();

        $select = $this->db->select()->from(array('progetti' => 'progetti'), array(
                    'NomeProgetto' => 'progetti.Nome',
                    'IndirizzoProgetto' => 'progetti.Indirizzo',
                    'Note' => 'progetti.Note',
                    'DataInizio' => 'progetti.Inizio',
                    'DataFine' => 'progetti.Fine',
                    'ggProg' => 'DATEDIFF(progetti.Fine, progetti.Inizio)',
                    'Budget' => 'progetti.Budget'))
                ->joinLeft(array('prog_steps' => 'prog_steps'), 'prog_steps.idProgetto = progetti.idProgetto', array(
                    'Step' => 'prog_steps.Step',
                    'InizioStep' => 'prog_steps.Inizio',
                    'FineStep' => 'prog_steps.Fine',
                    'NoteStep' => 'prog_steps.Note',
                    'ggStep' => 'DATEDIFF(prog_steps.Fine, prog_steps.Inizio)'))
                ->joinLeft(array('richieste' => 'richieste'), 'richieste.idProgStep = prog_steps.idProgStep', array(
                ))
                ->joinLeft(array('qualifiche' => 'qualifiche'), 'qualifiche.idQualifica = richieste.idQualifica', array(
                    'Qualifica' => 'qualifiche.Descrizione'
                ))
                ->joinLeft(array('personale' => 'personale'), 'personale.idQualifica = qualifiche.idQualifica', array(
                    'Lavoratore' => 'CONCAT(personale.Nome, " ", personale.Cognome)'
                ))
                ->joinLeft(array('tab_schemi_lav' => 'tab_schemi_lav'), 'tab_schemi_lav.idTabSchemiLav = richieste.idTabSchemiLav', array(
                    'Schemalav' => 'descSchemaLav'
                ))
                ->where('progetti.idProgetto="' . $currId . '"')
                ->order(array('prog_steps.idProgStep', 'qualifiche.Descrizione',
            'CONCAT(personale.Cognome, " ", personale.Nome)'));

        $qry = $this->db->query($select);
        $reqlist = $qry->fetchAll();
//        $request=$qry->fetchRow();
        $reqMain = $reqlist[0];
//        $myPdf->calcPub($this->db, $request['idPublisher'], $request['Protocollo']);
//        $this->view->Intestazione = $myPdf->getIntest();
//        $this->view->Firma = $myPdf->getFirma();
//        $this->view->Foother = $myPdf->getFoot();
        $tmpHtml = $this->view->render('dashboard/print.phtml');
//        $mpdf->WriteHTML($tmpHtml);

        $tmpPages = explode('#Sezione', $tmpHtml);
        $pages = array();
        foreach ($tmpPages as $page)
            {
            $page = ltrim($page);
            $sezName = strtok($page, ' ');
            $pages[$sezName] = '<!-- ' . $page;
            }
        $pages['footer1'] = $myPdf->substPar($pages['footer1'], $reqMain);
        $mpdf->DefHTMLFooterByName('Footer1', $pages['footer1']);
        $pages['footer2'] = $myPdf->substPar($pages['footer2'], $reqMain);
        $mpdf->DefHTMLFooterByName('Footer2', $pages['footer2']);
        $mpdf->SetHTMLFooterByName('Footer1');
        $pages['main1'] = $myPdf->substPar($pages['main1'], $reqMain);
        $mpdf->WriteHTML($pages['main1']);
        $mpdf->SetHTMLFooterByName('Footer2');

        $currStep = '';
        $buildPage = '';
        foreach ($reqlist as $request)
            {
            if ($request['Step'] != $currStep)
                {
                $currStep = $request['Step'];
                $tmpPage = $myPdf->substPar($pages['step_line'], $request);
                $buildPage .= $tmpPage;
//                $mpdf->WriteHTML($tmpPage);
                }
            $tmpPage = $myPdf->substPar($pages['richieste'], $request);
            $buildPage .= $tmpPage;
//            $mpdf->WriteHTML($tmpPage);
            }
//        $mpdf->WriteHTML($pages['endtable']);
        $mpdf->WriteHTML($buildPage);
        $mpdf->WriteHTML($pages['endtable']);
        $this->view->Intestazione = $this->view->baseUrl() . '/images/nao.png';

//        $tabGuestKey = $this->findTableGuest('rde');
//        if ($tabGuestKey != '')
//            {
//            $guestFiles = $this->findGuestFiles($tabGuestKey, $currId);
//            if (is_array($guestFiles) && count($guestFiles) > 0)
//                {
//                $mpdf->AddPage();
//                $pages['alleg_title'] = $myPdf->substPar($pages['alleg_title'], $request);
//                $mpdf->WriteHTML($pages['alleg_title']);
//                foreach ($guestFiles as $key => $currFile)
//                    {
//                    $pages['alleg_line'] = $myPdf->substPar($pages['alleg_line'], $currFile);
//                    $mpdf->WriteHTML($pages['alleg_line']);
//                    }
//                $pages['alleg_end'] = $myPdf->substPar($pages['alleg_end'], $request);
//                $mpdf->WriteHTML($pages['alleg_end']);
//                }
//            }
        $PdfRender = $mpdf->Output('progetto_' . $currId . '.pdf', 'S');
        $this->getResponse()
                ->setHeader('Content-type', 'application/pdf')
                ->setHeader('Pragma', 'public') // required
                ->setHeader('Expires', '0')
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->setHeader('Cache-Control', 'private', false) // required for certain browsers
                ->setHeader('Content-Type', '$ctype')
//header("Content-Disposition: attachment; filename=".basename($outputFile).";" );
                ->setHeader('Content-Transfer-Encoding', 'binary')
//           ->setHeader('Content-Length', filesize($sourceFile))
                ->setBody($PdfRender);
    }

    public function saveProj1DashAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'progetto', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->saveData($formData))
                {
                $this->_redirect('/dashboard/riassunto/id/' . $formData['idProgetto']);
                }
            else
                {
                $defaultNamespace->currProj1 = $currForm;
                }
            $this->_redirect('/dashboard/riassunto/id/' . $formData['idProgetto']);
            }
//            
    }

    public function saveProj2DashAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'progetto2', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->saveData($formData))
                {
                $this->_redirect('/dashboard/riassunto/id/' . $formData['idProgetto']);
                }
            else
                {
                $defaultNamespace->currProj2 = $currForm;
                }
            $this->_redirect('/dashboard/riassunto/id/' . $formData['idProgetto']);
            }
        // action body
    }

    public function totGraphAction()
    {
        $this->_helper->layout()->enableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
        $this->setGraphTitle("Totals");
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
        $series = array('tot'=>array('title'=>'Current Totals', 'values'=>''),
                        'totcontr'=>array('title'=>'Contract Totals', 'values'=>''));
        $line1='[';
        $line2='[';
        for ($y = 1; $y < (count($GGLavArrTot) - 5); $y++)
            {
            $newKey = $GGTitles[$y + 4];
            $newGGLavTot[$newKey] = $GGLavArrTot[$y];
            $newGGLavTotContr[$newKey] = $GGLavArrTotContr[$y];
            $newKeyArr = explode('/', $newKey);
            if($line1 != '[')
                {
                $line1 .= ',';                
                }
            if($line2 != '[')
                {
                $line2 .= ',';                
                }
            $line1 .= "['".$newKeyArr[1].'-'.
                          $newKeyArr[0]."-01',". $GGLavArrTot[$y].']';    
            $line2 .= "['".$newKeyArr[1].'-'.
                          $newKeyArr[0]."-01',". $GGLavArrTotContr[$y].']';    
            }
        $line1 .= ']';
        $series['tot']['values'] = $line1;
        $line2 .= ']';
        $series['totcontr']['values']  = $line2;
        $revArr = $this->buildRevArray($currId);
        foreach ($revArr as $serKey => $currSerie)
            {
            $valKey = $serKey>0 ? $serKey-1 : '';
            $series['tot_'.$serKey]=array('title'=>'Rev. '. $valKey .' Tot.', 'values'=>'');
            $series['totcontr_'.$serKey]=array('title'=>'Rev. '. $valKey .' Tot. Contr.', 'values'=>'');
            $line1='[';
            $line2='[';
            foreach ($currSerie['Values'] as $key => $values)
                {
                if($line1 != '[')
                    {
                    $line1 .= ',';                
                    }
                if($line2 != '[')
                    {
                    $line2 .= ',';                
                    }
                $line1 .= "['".$key."'," . $values['Totale'].']';    
                $line2 .= "['".$key."'," . $values['TotaleContratto'].']';    
                }
            $line1 .= ']';
            $series['tot_'.$serKey]['values'] = $line1;
            $line2 .= ']';
            $series['totcontr_'.$serKey]['values']  = $line2;
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

    public function progGraphAction()
    {
        $this->_helper->layout()->enableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->_helper->viewRenderer('/dashboard/totGraph', null, true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->findProject($currId);
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
        $series = array('prog'=>array('title'=>'Current Progressive', 'values'=>''),
                        'contr'=>array('title'=>'Contract Progressive', 'values'=>''));
        $line1='[';
        $line2='[';
        for ($y = 1; $y < count($GGLavArrTot) - 5; $y++)
            {
            $newKey = $GGTitles[$y + 4];
            $prog += $GGLavArrTot[$y];
            $progContr += $GGLavArrTotContr[$y];
            $newGGLavTot[$newKey] = $prog;
            $newGGLavTotContr[$newKey] = $progContr;
            
            $newKeyArr = explode('/', $newKey);
            if($line1 != '[')
                {
                $line1 .= ',';                
                }
            if($line2 != '[')
                {
                $line2 .= ',';                
                }
            $line1 .= "['".$newKeyArr[1].'-'.
                          $newKeyArr[0]."-01',". $prog.']';    
            $line2 .= "['".$newKeyArr[1].'-'.
                          $newKeyArr[0]."-01',". $progContr.']';    
            }
        $line1 .= ']';
        $series['prog']['values'] = $line1;
        $line2 .= ']';
        $series['contr']['values']  = $line2;
        $revArr = $this->buildRevArray($currId);
        foreach ($revArr as $serKey => $currSerie)
            {
            $valKey = $serKey>0 ? $serKey-1 : '';
            $series['prog_'.$serKey]=array('title'=>'Rev. '. $valKey .' Progressive', 'values'=>'');
            $series['contr_'.$serKey]=array('title'=>'Rev. '. $valKey .' Contr. Prog.', 'values'=>'');
            $line1='[';
            $line2='[';
            foreach ($currSerie['Values'] as $key => $values)
                {
                if($line1 != '[')
                    {
                    $line1 .= ',';                
                    }
                if($line2 != '[')
                    {
                    $line2 .= ',';                
                    }
                $line1 .= "['".$key."'," . $values['Progressivo'].']';    
                $line2 .= "['".$key."'," . $values['ProgrContratto'].']';    
                }
            $line1 .= ']';
            $series['prog_'.$serKey]['values'] = $line1;
            $line2 .= ']';
            $series['contr_'.$serKey]['values']  = $line2;
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

    private function buildRevArray($currId)
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
            'WHERE savetot.idProgetto = "' . $currId . '" '.
            'ORDER BY savetot.Versione,savetot_dett.Anno,savetot_dett.Mese';
        $allRev = $this->db->fetchAll($sql);
        if(!$allRev) {return array();}
        $currRev = '';
        $retArr = array();
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

}


