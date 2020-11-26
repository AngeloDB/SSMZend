<?php

class Custom_Print
    {

    private $mpdf;
    
    public function init()
        {
        }
        
    public function calcPub($db, $idPublisher, $notBozza)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        if($idPublisher == 0)
            $idPublisher=$defaultNamespace->user['id_utente'];
        $select = $db->select()->from(
            array('utenti' => 'utenti'),
                    array('id_utente'))
            ->join(array('tab_guests' => 'tab_guests'), 
                    'tab_guests.Tablename="utenti"', array())
            ->joinleft(array('alleguests' => 'alleguests'), 
                    'alleguests.idTab_guest=tab_guests.idTab_guest 
                AND alleguests.idGuestkey=utenti.id_utente', 
                    array('Guest'))
            ->joinleft(array('allegati' => 'allegati'), 
                    'allegati.idAllegato=alleguests.idAllegato', 
                    array('Storename'))
            ->where('utenti.id_utente="' . $idPublisher. '"');
        $qry = $db->query($select);
        $reqlist = $qry->fetchAll();
        $request = $reqlist[0];
        if($notBozza == 0)  // Bozza!
            {
            $request['intestazione']='../public/images/cartaintestata/bozza/logoheader.jpg';
            $request['firma']='../public/images/cartaintestata/bozza/firmacoordinatore.jpg';
            $request['piede']='../public/images/cartaintestata/bozza/logofooter.jpg';
            }
        else if($request['Storename']=='')  // Generico
            {
            $request['intestazione']='../public/images/cartaintestata/generica/logoheader.jpg';
            $request['firma']='../public/images/cartaintestata/generica/firmacoordinatore.jpg';
            $request['piede']='../public/images/cartaintestata/generica/logofooter.jpg';
            }
        else    // Carta intestata propria      
            {
            $request = $reqlist[0];
            foreach($reqlist as $curReq)
                {
                switch ($curReq['Guest'])
                    {
                    case 'intestazione':
                        $request['intestazione']='../uploads/'.$curReq['Storename'];
                        break;
                    case 'firma':
                        $request['firma']='../uploads/'.$curReq['Storename'];
                        break;
                    case 'piede':
                        $request['piede']='../uploads/'.$curReq['Storename'];
                        break;
                    default:
                        break;
                    }
                }
            }    
        $defaultNamespace->user['intestazione']=$request['intestazione'];
        $defaultNamespace->user['firma']=$request['firma'];
        $defaultNamespace->user['piede']=$request['piede'];
        $this->currUser=$defaultNamespace->user;
        }
        
    public function getMpdfCard()
        {
        define('_MPDF_PATH', APPLICATION_PATH . '/../library/Mpdf/');
//        include("../library/Mpdf/mpdf.php");
        var_dump(__DIR__);
        die();
        require_once __DIR__ . '../library/composer/vendor/autoload.php';

//        $mpdf = new \Mpdf\Mpdf();
//        return(new mPDF('UTF-8', array(85.6, 53.98), 0, '', 1, 1, 1, 1, 0, 0));
        return(new \Mpdf\Mpdf('UTF-8', array(85.6, 53.98), 0, '', 1, 1, 1, 1, 0, 0));
        }
        
    public function getMpdf()
        {
        define('_MPDF_PATH', APPLICATION_PATH . '/../library/Mpdf/');
//        require_once("../library/Mpdf/mpdf/src/Mpdf.php");
//        require_once '/vendor/autoload.php';
//        var_dump(__DIR__);
//        var_dump(APPLICATION_PATH . '/../library/composer/vendor/composer/autoload.php');
//        die();
//        require_once realpath(APPLICATION_PATH . '/../library/composer/vendor/composer/autoload.php');

        return(new \Mpdf\Mpdf());
//        return(new \Mpdf\Mpdf('UTF-8', 'A4', 0, '', 5, 5, 15, 40, 15, 8));
        }

    public function getIntest()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        return($defaultNamespace->user['intestazione']);
        }

    public function getFirma()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        return($defaultNamespace->user['firma']);
        }

    public function getFoot()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        return($defaultNamespace->user['piede']);
        }

    public function substPar($funct, $arrPar)
        {
        $out = '';
        if (strpos($funct, '{{') === false)
            {
            return $funct;
            }
        else
            {
            $arrTmp = explode('{{', $funct);
            foreach ($arrTmp as $currChunk)
                {
                if (strpos($currChunk, '}}') === false)
                    {
                    $out.=$currChunk;
                    }
                else
                    {
                    $arrChunk = explode('}}', $currChunk);
// {{Par, Format}} 08/03/2012                    
                    if (strpos($arrChunk[0], ',') === false)
                        {
                        $out.=$this->stripInvalid($arrPar[$arrChunk[0]]) . $arrChunk[1];
                        }
                    else
                        {
                        $arrForm = explode(',', $arrChunk[0]);
                        $out.=$this->format($this->stripInvalid($arrPar[trim($arrForm[0])]), trim($arrForm[1])) . $arrChunk[1];
                        }
                    }
                }
            return $out;
            }
        }

    private function format($par, $form)
        {
        switch (strtoupper(trim($form)))
            {
            case 'ITALDATE':
                if ($par != '')
                    {
                    $arrDate = explode('-', $par);
                    $outValue = $arrDate[2] . '/' . $arrDate[1] . '/' . $arrDate[0];
                    }
                else
                    {
                    $outValue = $par;
                    }
                break;
            default :
                $outValue = $par;
            }
        return $outValue;
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
        if($outString == '')
            $outString='&nbsp;';
        return $outString;
        }

    }