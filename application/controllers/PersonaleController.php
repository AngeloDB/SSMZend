<?php

class PersonaleController extends Zend_Controller_Action
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

    public function listAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $request = $this->getRequest();
        $currMode = $request->getParam('mode');
        $currGrid = new Custom_XmlGrid();
//        $grid = $currGrid->getGrid($this, 'personale', 0, array('mode' => $currMode));
        $defaultNamespace->listMode = $currMode;
        if($currMode == 'ext')
            {
            $grid = $currGrid->getGrid($this, 'personale_ext', 0, array('mode' => $currMode));
            $defaultNamespace->cant2 = $translate->_("Personale esterno grid");
            }
        elseif($currMode == 'int')    
            {
            $grid = $currGrid->getGrid($this, 'personale_int', 0, array('mode' => $currMode));
            $defaultNamespace->cant2 = $translate->_("Personale interno grid");
            }
        else
            {
            $grid = $currGrid->getGrid($this, 'personale', 0, array('mode' => $currMode));
            $defaultNamespace->cant2 = $translate->_("Personale tutto grid");
            }
        $grid->addClassCellCondition('Scadenza','"{{ggScadenza}}" <> "" && "{{ggScadenza}}" < "0"',"evid_red");
        $grid->addClassCellCondition('Scadenza','"{{ggScadenza}}" <> "" && "{{ggScadenza}}" < "30" && "{{ggScadenza}}" > "0"',"evid_yellow");
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/grid-evid.css');
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
//
        }

    public function newAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currMode = $request->getParam('mode');
        switch ($currMode) {
            case "ext":
                $defaultNamespace->cant2 = "New Person - External";
                break;

            case "int":
                $defaultNamespace->cant2 = "New Person - Internal";
                break;

            default:
                $defaultNamespace->cant2 = "New Person";
                break;
        }
        $defaultNamespace->title1 = ' - Nuovo personale';
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'personale', 0, array('mode' => $currMode));
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/personale/new');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->saveGuests('personale', 'qualifiche', $currId, $formData['idMultiQual']);
                $this->_redirect('/personale/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
        $this->view->form = $form;
        $this->view->formTitle = $currForm->getNewTitle();
        $this->view->javascript = $currForm->getJavascript();
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
                '/styles/styles.css');
        }

    public function editAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'personale', $currId);
        $currForm->setCurrAction($defaultNamespace->baseUrl . '/personale/edit');
        $form = $currForm->getForm();
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->saveGuests('personale', 'qualifiche', $formData['idPersonale'], $formData['idMultiQual']);
                $this->_redirect('/personale/list');
                }
            else
                {
                $form->populate($formData);
                }
            }
//        $form = $currForm->getForm();
        $form->getElement('idMultiQual')->setValue($this->getGuests('personale', 'qualifiche', $currId));
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
                '/styles/styles.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/assets/prettify.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jquery-ui-m2/jquery.multiselect.filter.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
//        $defaultNamespace->title1 = $title;
        }

    private function getGuests($currTab, $currHost, $currKey)
        {
        $sql = 'SELECT guesthost.idHost AS Selected FROM guesthost
                JOIN tab_guests ON tab_guests.idTab_guest=guesthost.idTab_guest 
                JOIN tab_hosts ON tab_hosts.idTab_host=guesthost.idTab_host 
                WHERE guesthost.idGuest="' . $currKey .
                '" AND tab_guests.TableName="' . $currTab . '"' .
                ' AND tab_hosts.TableName="' . $currHost . '"';
        $selData = $this->db->fetchAll($sql);
        $selArr = array();
        foreach ($selData as $selVal)
            {
            $selArr[] = $selVal['Selected'];
            }
        return $selArr;
        }

    private function saveGuests($currTab, $currHost, $currKey, $currMulti)
        {
        $sql = 'SELECT idTab_guest FROM tab_guests WHERE TableName = "' . $currTab . '"';
        $verbData = $this->db->fetchRow($sql);
        $currTabGuest = $verbData['idTab_guest'];
        $sql = 'SELECT idTab_host FROM tab_hosts WHERE TableName = "' . $currHost . '"';
        $verbData = $this->db->fetchRow($sql);
        $currTabHost = $verbData['idTab_host'];
        $guestHostTable = new Zend_Db_Table('guesthost');
        $where = array($this->db->quoteInto('idTab_guest = ?', $currTabGuest),
            $this->db->quoteInto('idTab_host = ?', $currTabHost),
            $this->db->quoteInto('idGuest = ?', $currKey));
        $guestHostTable->delete($where);
//        $guestHostTable->delete($guestHostTable->getAdapter()->quoteInto(
//                        'idTab_guest = "'.$currTabGuest.
//                        '" AND idTab_host = "'.$currTabHost.
//                        '" AND idGuest= "'.$currKey.'" ' 
//                        ));
        foreach ($currMulti as $value)
            {
            $guestHostTable->insert(array(
                'idTab_guest' => $currTabGuest,
                'idTab_host' => $currTabHost,
                'idGuest' => $currKey,
                'idHost' => $value));
            }
//
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
        $mpdf->AddPage();

        $this->view->Intestazione = 'images/logogruppo_test.jpg';
        $this->view->currPers = $this->getPersImage($currId);
        $tmpHtml = $this->view->render('personale/print.phtml');

        $tmpPages = explode('#Sezione', $tmpHtml);
        $pages = array();
        foreach ($tmpPages as $page)
            {
            $page = ltrim($page);
            $sezName = strtok($page, ' ');
            $pages[$sezName] = '<!-- ' . $page;
            }
        $pages['main1'] = $myPdf->substPar($pages['main1'], $reqMain);
        $mpdf->WriteHTML($pages['main1']);

        $PdfRender = $mpdf->Output('scheda_' . $currId . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        $this->getResponse()
                ->setHeader('Content-type', 'application/pdf')
                ->setHeader('Pragma', 'public') // required
                ->setHeader('Expires', '0')
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->setHeader('Cache-Control', 'private', false) // required for certain browsers
                ->setHeader('Content-Type', '$ctype')
                ->setHeader('Content-Transfer-Encoding', 'binary')
                ->setBody($PdfRender);
        }

    public function printJpegAction()
        {
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $currPers = $this->getPersImage($currId);
        $persImgFile = 'uploads/' . $currPers['Allegato'];
//        $persImgFile = 'http://' . $_SERVER['HTTP_HOST'] .
//                $this->view->baseUrl() . '/uploads/' . $currPers['Allegato'];
        if ($currPers['Allegato'] != '' && file_exists($persImgFile))
            {
            $persImgSize = getimagesize($persImgFile);
            $persImg = imagecreatefromjpeg($persImgFile);
            }
        else
            {
            $persImgSize = array(1, 1);
            $persImg = $imgDest = ImageCreateTrueColor(1, 1);
            ;
            }
        $type = 'image/jpeg';


        $atImgFile = 'images/at-sign-for-email-at-email-sign-icon-2.png';
//        $atImgFile = 'http://' . $_SERVER['HTTP_HOST'] .
//                $this->view->baseUrl() . '/images/' . 
//                "at-sign-for-email-at-email-sign-icon-2.png";
        if (file_exists($atImgFile))
            {
            $atImgSize = getimagesize($atImgFile);
            $srcAtImg = imagecreatefrompng($atImgFile);
            $atImg = ImageCreateTrueColor(25, 25);
            $atWhite = imagecolorallocate($atImg, 0xFF, 0xFF, 0xFF);
            imagefilledrectangle($atImg, 0, 0, 25, 25, $atWhite);
            imagecopyresampled($atImg, $srcAtImg, 0, 0, 0, 0, 25, 25, $atImgSize[0], $atImgSize[1]);
            }
        else
            {
            $atImgSize = array(25, 25);
            $atImg = ImageCreateTrueColor(25, 25);
            ;
            }

        $phoneImgFile = 'images/phone-icon-image-phone-icon-080864-31.png';
//        $phoneImgFile = 'http://' . $_SERVER['HTTP_HOST'] .
//                $this->view->baseUrl() . '/images/' . 
//                "phone-icon-image-phone-icon-080864-31.png";
        if (file_exists($phoneImgFile))
            {
            $phoneImgSize = getimagesize($phoneImgFile);
            $srcPhoneImg = imagecreatefrompng($phoneImgFile);
            $phoneImg = ImageCreateTrueColor(35, 35);
            $phWhite = imagecolorallocate($phoneImg, 0xFF, 0xFF, 0xFF);
            imagefilledrectangle($phoneImg, 0, 0, 35, 35, $phWhite);
            $val1 = imagecopyresampled($phoneImg, $srcPhoneImg, 0, 0, 0, 0, 35, 35, $phoneImgSize[0], $phoneImgSize[1]);
            }
        else
            {
            $phoneImgSize = array(25, 25);
            $phoneImg = ImageCreateTrueColor(25, 25);
            }

        $imgDest = ImageCreateTrueColor(850, 550);
        $white = imagecolorallocate($imgDest, 0xFF, 0xFF, 0xFF);
        $black = imagecolorallocate($imgDest, 0x00, 0x00, 0x00);
        imagefilledrectangle($imgDest, 0, 0, 850, 550, $white);

        $sw = $persImgSize[0];
        $sh = $persImgSize[1];
        $dw = 430;
        $dh = 530;

        $sr = $sw / $sh;
        $dr = $dw / $dh;
        if ($sr > $dr)
            $dh = round($dw / $sr);
        else
            $dw = round($dh * $sr);

        $val1 = imagecopyresampled($imgDest, $persImg, 10, 10, 0, 0, $dw, $dh, $sw, $sh);
//        $ratio = 530.0 / $persImgSize[1];
//        $newImgWidth = $persImgSize[0] * $ratio;
//        $val1 = imagecopyresampled($imgDest, $persImg, 10, 10, 0, 0, $newImgWidth, 530, $persImgSize[0], $persImgSize[1]);

        $arrTxt = array(
            'Nome' => array(
                'ico' => '',
                'text' => $currPers['Nome'] . ' ' . $currPers['Cognome']
            ),
            'Qual' => array(
                'ico' => '',
                'text' => $currPers['Qualifica']
            ),
            'Email1' => array(
                'ico' => 'Email',
                'text' => $currPers['Email']
            ),
            'Email2' => array(
                'ico' => 'Email',
                'text' => $currPers['Email2']
            ),
            'Tel1' => array(
                'ico' => 'Tel',
                'text' => $currPers['TelMobile']
            ),
            'Tel2' => array(
                'ico' => 'Tel',
                'text' => $currPers['TelMobileLocal']
            ),
            'Company' => array(
                'ico' => '',
                'text' => $currPers['RagSoc']
            ),
        );

//                = array(
//            $currPers['Nome'] . ' ' . $currPers['Cognome'],
//            $currPers['Qualifica'],
//            'Email: ' . $currPers['Email'],
//            'Email 2: ' . $currPers['Email'],
//            'Tel: ' . $currPers['TelMobile'],
//            'Tel 2: ' . $currPers['TelMobileLocal'],
//            'Company: ' . $currPers['RagSoc']);

        $xPos = $dw + 50;
        $yPos = 40;
        $newLine = 30;
        foreach ($arrTxt as $line)
            {
            $txtOffset = 0;
            switch ($line['ico'])
                {
                case 'Email':
                    imagecopy($imgDest, $atImg, $xPos - 5, $yPos - 5, 0, 0, 25, 25);
                    $txtOffset = 30;
                    break;

                case 'Tel':
                    imagecopy($imgDest, $phoneImg, $xPos - 10, $yPos - 10, 0, 0, 35, 35);
                    $txtOffset = 30;
                    break;

                default:
                    $txtOffset = 0;
                    break;
                }
            imagestring($imgDest, 5, $xPos + $txtOffset, $yPos, $line['text'], $black);
            $yPos += $newLine;
            }

//        $response = $this->getFrontController()->getResponse();
//
//        $response->setHeader('Content-Description', 'File Transfer', true);
//        $response->setHeader('Content-Type', $type, true);
//        $response->setHeader('Content-Transfer-Encoding', 'binary', true);
//        $response->setHeader('Content-Disposition:attachment;filename="' .
//                $currPers['Nome'] . '-' . $currPers['Cognome'] . '.jpg"', '', false);
//        $response->setHeader('Expires', '0', true);
//        $response->setHeader('Cache-Control', 'max-age=3600, must-revalidate', true);
//        $response->setHeader('Pragma', 'public', true);
//        
//        $response->sendHeaders();

        header('Content-Description: File Transfer');
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="' .
                $currPers['Nome'] . '-' . $currPers['Cognome'] . '.jpg"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: max-age=0, must-revalidate');
        header('Pragma: public');
//        header('Content-Length: ' . filesize($outputFileName));


        imagejpeg($imgDest);
        exit;
        }

    private function getPersImage($currId)
        {
        $select = $this->db->select()->from(array('personale' => 'personale'), array(
                    'Nome' => 'personale.Nome',
                    'Cognome' => 'personale.Cognome',
                    'Email' => 'personale.Email',
                    'Email2' => 'personale.Email2',
                    'TelMobile' => 'personale.TelMobile',
                    'TelMobileLocal' => 'personale.TelMobileLocal'))
                ->join(array('qualifiche' => 'qualifiche'), 'qualifiche.idQualifica = personale.idQualifica', array(
                    'Qualifica' => 'qualifiche.Descrizione'))
                ->join(array('forn_pers' => 'forn_pers'), 'forn_pers.idFornPers = personale.idFornPers', array(
                    'RagSoc' => 'forn_pers.RagSoc'))
                ->join(array('tab_guests' => 'tab_guests'), "tab_guests.Tablename = 'personale'", array())
                ->joinLeft(array('alleguests' => 'alleguests'), "alleguests.idTab_guest = tab_guests.idTab_guest AND 
                    alleguests.idGuestkey = personale.idPersonale AND 
                    alleguests.Guest = 'Fototessera'", array())
                ->joinLeft(array('allegati' => 'allegati'), 'allegati.idAllegato = alleguests.idAllegato', array(
                    'idAllegato' => 'allegati.idAllegato',
                    'StoreName' => 'allegati.StoreName',
                    'Qualifica' => 'qualifiche.Descrizione',
                    'Allegato' => "SUBSTRING_INDEX( GROUP_CONCAT(allegati.StoreName) , ',', 1)"))
                ->group(array('personale.idPersonale'))
                ->where('personale.idPersonale="' . $currId . '"')
                ->order(array("CONCAT(personale.Cognome, ' ', personale.Nome)"));

        $qry = $this->db->query($select);
        $reqlist = $qry->fetchAll();
        return $reqlist[0];
        }

    public function riassuntoAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $request = $this->getRequest();
        $currId = $request->getParam('id');

        $sql = "SELECT personale.idPersonale,
                personale.Nome,
                personale.Cognome,
                personale.Email,
                personale.Email2,
                personale.TelMobile,
                personale.TelMobileLocal,
                forn_pers.RagSoc,
                allegati.idAllegato,
                allegati.StoreName,
                qualifiche.Descrizione AS Qualifica,
                SUBSTRING_INDEX( GROUP_CONCAT(allegati.StoreName) , ',', 1) AS Allegato
                FROM personale 
                LEFT JOIN qualifiche ON qualifiche.idQualifica = personale.idQualifica
                LEFT JOIN forn_pers ON forn_pers.idFornPers = personale.idFornPers 
                INNER JOIN tab_guests ON tab_guests.Tablename = 'personale' 
                LEFT JOIN alleguests ON alleguests.idTab_guest = tab_guests.idTab_guest AND 
                    alleguests.idGuestkey = personale.idPersonale AND alleguests.Guest = 'Fototessera'                    
                LEFT JOIN allegati ON allegati.idAllegato = alleguests.idAllegato 
                GROUP BY personale.idPersonale
                ORDER BY CONCAT(personale.Cognome, ' ', personale.Nome)";
//                WHERE (personale.idPersonale = "' . $currId . '") 
//                INNER JOIN qualifiche ON qualifiche.idQualifica = personale.idQualifica
//        $currPers = $this->db->fetchAll($sql);
        $allPers = $this->db->fetchAll($sql);
//        $key = array_search($currId, array_column($allPers, 'idPersonale'));
        $key = array_search($currId, array_map(function($element)
                    {
                    return $element['idPersonale'];
                    }, $allPers));
//        if (count($currPers) == 0)
        if ($key === false)
            {
            $this->_redirect('/personale/list');
            }

        $this->view->listMode = $defaultNamespace->listMode;
            
        $currPers = $allPers[$key];
        $this->view->currPers = $currPers;
        if ($key > 0)
            {
            $this->view->precPersId = $allPers[$key - 1]['idPersonale'];
            }
        else
            {
            $this->view->precPersId = '';
            }
        if ($key < (count($allPers) - 1))
            {
            $this->view->nextPersId = $allPers[$key + 1]['idPersonale'];
            }
        else
            {
            $this->view->nextPersId = '';
            }
        $this->findPers($currId);
        $this->setTitle();

        $currTab = $request->getParam('seltab');
        if ($currTab == '')
            {
            $currTab = 0;
            }
//        $defaultNamespace->currEdit = $currId;
        $this->view->currTab = $currTab;
//
//        $persFormScheda = new Custom_XmlForm();
//        $persFormScheda->initForm($this->db, 'personale_scheda', $currId);
//        $formScheda = $persFormScheda->getForm();
//        $this->view->persFormScheda = $formScheda;
//        $this->view->jsScheda = $persFormScheda->getJavascript();

        if ($defaultNamespace->Pers1 != '')
            {
            $persForm1 = $defaultNamespace->Pers1;
            $form1 = $persForm1->getForm();
            $defaultNamespace->Pers1 = '';
            }
        else
            {
            $persForm1 = new Custom_XmlForm();
            $persForm1->initForm($this->db, 'personale1', $currId);
            $form1 = $persForm1->getForm();
            $form1->getElement('idMultiQual')->setValue($this->getGuests('personale', 'qualifiche', $currId));
            $persForm1->setCurrAction($defaultNamespace->baseUrl . '/personale/save-pers1-riass');
            }
        $this->view->persForm1 = $form1;
        $this->view->js1 = $persForm1->getJavascript();


        if ($defaultNamespace->Pers2 != '')
            {
            $persForm2 = $defaultNamespace->Pers2;
            $form2 = $persForm1->getForm();
            $defaultNamespace->Pers2 = '';
            }
        else
            {
            $persForm2 = new Custom_XmlForm();
            $persForm2->initForm($this->db, 'personale2', $currId);
            $form2 = $persForm2->getForm();
            $persForm2->setCurrAction($defaultNamespace->baseUrl . '/personale/save-pers2-riass');
            }
//        $persForm2 = new Custom_XmlForm();
//        $persForm2->initForm($this->db, 'personale2', $currId);
//        $persForm2->setCurrAction($defaultNamespace->baseUrl . '/personale/save-pers2-riass');
//        $form2 = $persForm2->getForm();
        $this->view->persForm2 = $form2;
        $this->view->js2 = $persForm2->getJavascript();

        $passForm = new Custom_XmlForm();
        $currPass = $defaultNamespace->currPers['idPass'];
        if ($currPass == '')
            {
            $passForm->initForm($this->db, 'passaporto');
            $defaultNamespace->title1 = ' - Nuovo passaporto';
            $passForm->setCurrAction($defaultNamespace->baseUrl . '/personale/new-pass-riass');
            }
        else
            {
            $passForm->initForm($this->db, 'passaporto', $currPass);
            $defaultNamespace->title1 = ' - Edit passaporto';
            $passForm->setCurrAction($defaultNamespace->baseUrl . '/personale/save-pass-riass');
            }
        $form3 = $passForm->getForm();
        $this->view->passForm = $form3;
        $this->view->js3 = $passForm->getJavascript();

        $fotoForm = new Custom_XmlForm();
        $fotoForm->initForm($this->db, 'dipfoto', $currId);
        $fotoForm->setCurrAction($defaultNamespace->baseUrl . '/personale/save-foto-riass/id/' . $currId);
        $form4 = $fotoForm->getForm();
        $this->view->fotoForm = $form4;
        $this->view->js4 = $fotoForm->getJavascript();

        $rateGrid = new Custom_XmlGrid();
        $grid = $rateGrid->getGrid($this, 'pers_forn_pers', 0, array('currPers' => $defaultNamespace->currPers['idPersonale']));
        $this->view->js1Grid = $rateGrid->getJavascript();
        $this->view->rateGrid = $grid->deploy();
        $addLink = $rateGrid->getLink();
        if ($addLink != '')
            {
            $this->view->rateAddLink = $this->view->baseUrl() . $addLink;
            $this->view->rateAddLabel = $rateGrid->getLinkTitle();
            }

        $visaGrid = new Custom_XmlGrid();
        $grid = $visaGrid->getGrid($this, 'visti', 0, array('currPass' => $defaultNamespace->currPers['idPass']));
        $this->view->js2Grid = $visaGrid->getJavascript();
        $this->view->visaGrid = $grid->deploy();
        $addLink = $visaGrid->getLink();
        if ($addLink != '')
            {
            if ($defaultNamespace->currPers['idPass'] > 0)
                {
                $this->view->visaAddLink = $this->view->baseUrl() . $addLink;
                $this->view->visaAddLabel = $visaGrid->getLinkTitle();
                }
            else
                {
                $this->view->visaAddLink = '#';
                $this->view->visaAddLabel = $translate->_('Non si possono aggiungere visti senza un passaporto valido');
                }
            }

        $storyGrid = new Custom_XmlGrid();
        $grid = $storyGrid->getGrid($this, 'pers_jobs', 0, array('currPers' => $defaultNamespace->currPers['idPersonale']));
        $this->view->js3Grid = $visaGrid->getJavascript();
        $this->view->storyGrid = $grid->deploy();
        $addLink = $storyGrid->getLink();
        if ($addLink != '')
            {
            $this->view->storyAddLink = $this->view->baseUrl() . $addLink;
            $this->view->storyAddLabel = $storyGrid->getLinkTitle();
            }

        $retObj = $this->getGantt($currId);
        $this->view->ganttData = $retObj->ganttData;
        $this->view->allJobs = $retObj->allJobs;

        $config = Zend_Registry::get('config');
        $uploadPath = $config->uploads->get('localPath');
        $this->view->uploadPath = $uploadPath;
        
        

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
                '/jcrop/js/jquery.Jcrop.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jquery_gantt/js/jquery.fn.gantt.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-rotate/jQueryRotate.js');
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
                '/jquery_gantt/css/style.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/jcrop/css/jquery.Jcrop.min.css');
        }

    public function listJobsDettAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currRic = $request->getParam('ric');
        $this->findPers($currId);
        $this->setTitle();
        $currGrid = new Custom_XmlGrid();
//        $grid = $currGrid->getGrid($this, 'pers_jobs_detail');
        $grid = $currGrid->getGrid($this, 'pers_jobs_detail', 0, array('pers_id' => $currId, 'ric_id' => $currRic));
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/styles.css');
        $this->view->title = $currGrid->getTitle();
        $this->view->jsGrid = $currGrid->getJavascript();
        $this->view->grid = $grid->deploy();
        $this->view->cantiere = $defaultNamespace->cantiere;
        $this->view->currPers = $defaultNamespace->currPers;
        $this->view->listMode = $defaultNamespace->listMode;
        $addLink = $currGrid->getLink();
        if ($addLink != '')
            {
            $this->view->addLink = $this->view->baseUrl() . $addLink;
            $this->view->addLabel = $currGrid->getLinkTitle();
            }
        // action body
        }

    private function getGantt($currId)
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

        $ganttSql = "SELECT 
  jobs.idPersonale,
  giorni_lav.Anno,
  giorni_lav.Mese,
  giorni_lav.GiorniTarget AS Planned,
  giorni_lav.GiorniEff AS Actual,
  jobs.Inizio,
  jobs.Fine,
  personale.Nome,
  personale.Cognome,
  qualifiche.Descrizione AS Qualifica,
  prog_steps.Step,
  progetti.Nome AS Commessa,
  dett_giorni_lav.idDettGGLav,
  dett_giorni_lav.OreGG_01,
  dett_giorni_lav.TipoGG_01,
  dett_giorni_lav.FattGG_01,
  dett_giorni_lav.OreGG_02,
  dett_giorni_lav.TipoGG_02,
  dett_giorni_lav.FattGG_02,
  dett_giorni_lav.OreGG_03,
  dett_giorni_lav.TipoGG_03,
  dett_giorni_lav.FattGG_03,
  dett_giorni_lav.OreGG_04,
  dett_giorni_lav.TipoGG_04,
  dett_giorni_lav.FattGG_04,
  dett_giorni_lav.OreGG_05,
  dett_giorni_lav.TipoGG_05,
  dett_giorni_lav.FattGG_05,
  dett_giorni_lav.OreGG_06,
  dett_giorni_lav.TipoGG_06,
  dett_giorni_lav.FattGG_06,
  dett_giorni_lav.OreGG_07,
  dett_giorni_lav.TipoGG_07,
  dett_giorni_lav.FattGG_07,
  dett_giorni_lav.OreGG_08,
  dett_giorni_lav.TipoGG_08,
  dett_giorni_lav.FattGG_08,
  dett_giorni_lav.OreGG_09,
  dett_giorni_lav.TipoGG_09,
  dett_giorni_lav.FattGG_09,
  dett_giorni_lav.OreGG_10,
  dett_giorni_lav.TipoGG_10,   
  dett_giorni_lav.FattGG_10,
  dett_giorni_lav.OreGG_11,
  dett_giorni_lav.TipoGG_11,
  dett_giorni_lav.FattGG_11,
  dett_giorni_lav.OreGG_12,
  dett_giorni_lav.TipoGG_12,
  dett_giorni_lav.FattGG_12,
  dett_giorni_lav.OreGG_13,
  dett_giorni_lav.TipoGG_13,
  dett_giorni_lav.FattGG_13,
  dett_giorni_lav.OreGG_14,
  dett_giorni_lav.TipoGG_14,
  dett_giorni_lav.FattGG_14,
  dett_giorni_lav.OreGG_15,
  dett_giorni_lav.TipoGG_15,
  dett_giorni_lav.FattGG_15,
  dett_giorni_lav.OreGG_16,
  dett_giorni_lav.TipoGG_16,
  dett_giorni_lav.FattGG_16,
  dett_giorni_lav.OreGG_17,
  dett_giorni_lav.TipoGG_17,
  dett_giorni_lav.FattGG_17,
  dett_giorni_lav.OreGG_18,
  dett_giorni_lav.TipoGG_18,
  dett_giorni_lav.FattGG_18,
  dett_giorni_lav.OreGG_19,
  dett_giorni_lav.TipoGG_19,
  dett_giorni_lav.FattGG_19,
  dett_giorni_lav.OreGG_20,
  dett_giorni_lav.TipoGG_20,
  dett_giorni_lav.FattGG_20,
  dett_giorni_lav.OreGG_21,
  dett_giorni_lav.TipoGG_21,
  dett_giorni_lav.FattGG_21,
  dett_giorni_lav.OreGG_22,
  dett_giorni_lav.TipoGG_22,
  dett_giorni_lav.FattGG_22,
  dett_giorni_lav.OreGG_23,
  dett_giorni_lav.TipoGG_23,
  dett_giorni_lav.FattGG_23,
  dett_giorni_lav.OreGG_24,
  dett_giorni_lav.TipoGG_24,
  dett_giorni_lav.FattGG_24,
  dett_giorni_lav.OreGG_25,
  dett_giorni_lav.TipoGG_25,
  dett_giorni_lav.FattGG_25,
  dett_giorni_lav.OreGG_26,
  dett_giorni_lav.TipoGG_26,
  dett_giorni_lav.FattGG_26,
  dett_giorni_lav.OreGG_27,
  dett_giorni_lav.TipoGG_27,
  dett_giorni_lav.FattGG_27,
  dett_giorni_lav.OreGG_28,
  dett_giorni_lav.TipoGG_28,
  dett_giorni_lav.FattGG_28,
  dett_giorni_lav.OreGG_29,
  dett_giorni_lav.TipoGG_29,
  dett_giorni_lav.FattGG_29,
  dett_giorni_lav.OreGG_30,
  dett_giorni_lav.TipoGG_30,
  dett_giorni_lav.FattGG_30,
  dett_giorni_lav.OreGG_31,
  dett_giorni_lav.TipoGG_31,
  dett_giorni_lav.FattGG_31 
FROM
  richieste
INNER JOIN
  giorni_lav ON giorni_lav.idRichiesta = richieste.idRichiesta
LEFT OUTER JOIN
  dett_giorni_lav ON dett_giorni_lav.idRichiesta = richieste.idRichiesta AND
  dett_giorni_lav.Anno = giorni_lav.Anno AND dett_giorni_lav.Mese = giorni_lav.Mese
INNER JOIN
  jobs ON jobs.idRichiesta = richieste.idRichiesta
INNER JOIN
  personale ON personale.idPersonale = jobs.idPersonale
INNER JOIN
  qualifiche ON qualifiche.idQualifica = richieste.idQualifica
INNER JOIN
  prog_steps ON prog_steps.idProgStep = richieste.idProgstep
INNER JOIN
  progetti ON progetti.idProgetto = prog_steps.idProgetto
WHERE
  (jobs.idPersonale = " . $currId . " AND (giorni_lav.GiorniTarget > 0 OR giorni_lav.GiorniEff > 0))
ORDER BY progetti.Nome, prog_steps.Step, qualifiche.Descrizione, giorni_lav.Anno, giorni_lav.Mese";
        $allJobs = $this->db->fetchAll($ganttSql);
        if (count($allJobs) > 0)
            {
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
                    $ganttData .= "{ \"name\": \"" . 
                            $currJob['Commessa'] . ' - ' .
                            $currJob['Step'] . '","desc": "' . 
                            $currJob['Qualifica'] . '"  ,"values": [';
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
                        if ($currJob['TipoGG_' . $currDay] != '' && $currJob['FattGG_' . $currDay] != 'X')
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
                            $currOre = $currJob['OreGG_' . $currDay] == 0 ? 0 : $currJob['OreGG_' . $currDay];
                            $currTipo = $currJob['TipoGG_' . $currDay] == '' ? '' : $tipiGG[$currJob['TipoGG_' . $currDay]];
                            $currFatt = $currJob['FattGG_' . $currDay] == '' ? '' : $fattGG[$currJob['FattGG_' . $currDay]];
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
        $retObj = new stdClass();
        $retObj->ganttData = $ganttData;
        $retObj->allJobs = $allJobs;
        return $retObj;
//        return $ganttData;
        }

    private function findPers($currId)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->currProject = '';
        $defaultNamespace->currStep = '';
        if ($currId <> '')
            {
            $sql = 'SELECT personale.*, passaporti.idPass FROM personale ' .
                    'LEFT JOIN passaporti ON passaporti.idPersonale = personale.idPersonale ' .
                    'WHERE personale.idPersonale = "' . $currId . '"';
            $currPers = $this->db->fetchRow($sql);
            $defaultNamespace->currPers = $currPers;
            }
//            
        }

    private function setTitle()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $defaultNamespace->cant2 = $defaultNamespace->currPers['Nome'] .
                " " . $defaultNamespace->currPers['Cognome'];
        }

    public function savePers1RiassAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'personale1', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->saveGuests('personale', 'qualifiche', $formData['idPersonale'], $formData['idMultiQual']);
                $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/1');
                }
            else
                {
                $defaultNamespace->currPers1 = $currForm;
                }
            $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/1');
            }
//            
        }

    public function saveVisaRiassAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'personale', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/2');
                }
            else
                {
                $defaultNamespace->currPers2 = $currForm;
                }
            $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/2');
            }
//            
        }

    public function savePers2RiassAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'personale2', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->saveData($formData))
                {
                $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/2');
                }
            else
                {
                $defaultNamespace->currPers2 = $currForm;
                }
            $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/2');
            }
//            
        }

    public function savePassRiassAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'passaporto', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currForm->saveData($formData))
                {
                $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/4');
                }
            else
                {
                $defaultNamespace->currPass = $currForm;
                }
            $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/4');
            }
//            
        }

    public function newPassRiassAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'passaporto', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if ($currId = $currForm->insertData($formData))
                {
                $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/4');
                }
            else
                {
                $defaultNamespace->currPass = $currForm;
                }
            $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/4');
            }
//
        }

    public function saveFotoRiassAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $currForm = new Custom_XmlForm();
        $currForm->initForm($this->db, 'dipfoto', $currId);
        if ($this->_request->isPost())
            {
            $formData = $this->_request->getPost();
            if (count($_FILES == 0) && ($formData['w'] != '' || $formData['w'] != '' || $formData['CurrImgRotation'] > 0))
                {
                $yRatio = floatval($formData['NaturalImgHeight'] . '.0') /
                        floatval($formData['CurrImgHeight'] . '.0');
                $xRatio = floatval($formData['NaturalImgWidth'] . '.0') /
                        floatval($formData['CurrImgWidth'] . '.0');
                $startX = round(floatval($formData['x1'] . '.0') * $xRatio);
                $startY = round(floatval($formData['y1'] . '.0') * $yRatio);
                $imgHeight = round(floatval($formData['h'] . '.0') * $yRatio);
                $imgWidth = round(floatval($formData['w'] . '.0') * $yRatio);
                $jpeg_quality = 100;
                $origImgName = $formData['CurrAllegato'];
                $newName = sprintf("%04x%s", hexdec(substr(md5(microtime()), 0, 4)), substr($origImgName, 4));
                $src = 'uploads/' . $formData['CurrAllegato'];
                $dest = 'uploads/' . $newName;
                $imgOrig = imagecreatefromjpeg($src);
                if ($formData['CurrImgRotation'] > 0)
                    {
                    $imgOrig = imagerotate($imgOrig, 360.0 - floatval($formData['CurrImgRotation']), 0);
                    $startX = 0;
                    $startY = 0;
                    if ($formData['CurrImgRotation'] == 180)
                        {
                        $imgWidth = $formData['NaturalImgWidth'];
                        $imgHeight = $formData['NaturalImgHeight'];
                        }
                    else
                        {
                        $imgHeight = $formData['NaturalImgWidth'];
                        $imgWidth = $formData['NaturalImgHeight'];
                        }
                    }
                $imgDest = ImageCreateTrueColor(300, 400);
                $val1 = imagecopyresampled($imgDest, $imgOrig, 0, 0, $startX, $startY, 300, 400, $imgWidth, $imgHeight);
                $val2 = imagejpeg($imgDest, $dest, $jpeg_quality);
                $updSql = "UPDATE allegati SET StoreName = '" . $newName .
                        "' WHERE idAllegato = '" . $formData['CurrIdAllegato'] . "';";
                $this->db->query($updSql);
                $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/3');
                }
            elseif (count($_FILES > 0))
                {
                unset($formData['x1']);
                unset($formData['y1']);
                unset($formData['x2']);
                unset($formData['y2']);
                unset($formData['w']);
                unset($formData['h']);
                $currForm->saveData($formData);
                $this->_redirect('/personale/riassunto/id/' . $formData['idPersonale'] . '/seltab/3');
                }
            }
//            
        }

    public function printGanttAction()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currGantt = $request->getParam('outgantt');
//        var_dump($currGantt);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $config = Zend_Registry::get('config');
        $localPath = $config->downloads->get('localPath');

//require_once 'dompdf/autoload.inc.php';
//use Dompdf/Dompdf;
//        $newNamespace = new Zend_Session_Namespace('Dompdf/Dompdf');
// instantiate and use the dompdf class
//        define('_MPDF_PATH', APPLICATION_PATH . '/../library/Dompdf/');
//        include("../library/dompdf/autoload.inc.php");
//$dompdf = new Dompdf();
//$dompdf->loadHtml('hello world');
//
//// (Optional) Setup the paper size and orientation
//$dompdf->setPaper('A4', 'landscape');
//
//// Render the HTML as PDF
//$dompdf->render();
//
//// Output the generated PDF to Browser
//$dompdf->stream();
//        
//        
//        exit;




        $myPdf = new Custom_Print();
        $mpdf = $myPdf->getMpdf();
        $css = file_get_contents($localPath . '/../css/pdf.css');
        $mpdf->WriteHTML($css, 1);
        $css = file_get_contents($localPath . '/../css/printjs.css');
        $mpdf->WriteHTML($css, 1);
        $css = file_get_contents($localPath . '/../jquery_gantt/css/style.css');
        $mpdf->WriteHTML($css, 1);
        $mpdf->AddPage();
        $mpdf->WriteHTML($currGantt);
        $currId = "1";
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

    public function deleteAction()
        {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $sql = "DELETE FROM personale WHERE idPersonale='" . $currId . "'";
        $qry = $this->db->query($sql);
        $this->_redirect('/personale/list');
        // action body
        }

    }
