<?php

class Custom_Sicureasy
    {
//    private $rClient = 'http://marcatempo.sipracloud.it/api/report_stub.ashx';
//    private $dClient = 'http://marcatempo.sipracloud.it/api/report_dettaglio.ashx';
//    private $aClient = 'http://marcatempo.sipracloud.it/api/manage_user.ashx';
//    private $lName = "guest";
//    private $lPassw = "D@mi@n0";
    private $rClient = '';
    private $dClient = '';
    private $aClient = '';
    private $lName = "";
    private $lPassw = "";

    private $header = 
'<?xml version="1.0" encoding="UTF-8"?>
<!--Document created by: Italo -->
<serequest>';

    private function buildAction($act)
        {
        return '
    <azione>
        <lname>'.$this->lName.'</lname>
        <lpa>'.$this->lPassw.'</lpa>
        <myact>'.$act.'</myact>
    </azione>   
        ';
        }

    private function send($Xml, $act)
        {
        switch ($act)
            {
            case 'a':
                $client = new Zend_Http_Client($this->aClient);
                break;

            case 'd':
                $client = new Zend_Http_Client($this->dClient);
                break;

            case 'r':
                $client = new Zend_Http_Client($this->rClient);
                break;

            default:
                $client = new Zend_Http_Client($this->aClient);
                break;
            }
        $client->setAdapter("Zend_Http_Client_Adapter_Curl");
        $client->setHeaders('User-Agent', "MyUserAgent/1.1");
        $retVal='';
        try
            {
            $client->setRawData($Xml, 'text/xml');
            $retVal = $client->request('POST');
            }
        catch (Exception $e)
            {
            $retVal = "Error: " . var_export($e, true);
            }
//        var_dump($response);
//        die();
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $uploadPath = $config->downloads->get('localPath');
        $stream = @fopen($uploadPath.'/sicureasy.log', 'a', false);
        if (! $stream) 
            {
            throw new Exception('Failed to open stream');
            }
     
        $writer = new Zend_Log_Writer_Stream($stream);
        $logger = new Zend_Log($writer);
        ob_start();
        var_dump($Xml);
        var_dump($retVal);
        $result = ob_get_clean();

        $logger->info($result);    
            
        return $retVal;
        }

    function __construct()
        {
        $config = Zend_Registry::get('config');
        $this->rClient=$config->sicureasy->rClient;
        $this->dClient=$config->sicureasy->dClient;
        $this->aClient=$config->sicureasy->aClient;
        $this->lName=$config->sicureasy->lName;
        $this->lPassw=$config->sicureasy->lPassw;
//        $this->db = $currDb;
//        Zend_Db_Table::setDefaultAdapter($this->db);
        }

    public function addLav($currLav=array())
        {
        $out=$this->header.$this->buildAction("w");
        $out.='<contenuto>
        <nome>'.$currLav['Nome'].'</nome>
        <cognome>'.$currLav['Cognome'].'</cognome>
        <codicefiscale>'.$currLav['Codfisc'].'</codicefiscale>
        <data_nascita>'.$currLav['Nascitaalternate'].'</data_nascita>
        <stato>'.$currLav['Stato'].'</stato>
        <coll_id_se>'.$currLav['idDipendente'].'</coll_id_se>
    </contenuto>
</serequest>';
        return($this->send($out, 'a'));
        }

    public function qryLav($par=array())
        {
        $out=$this->header.$this->buildAction("r");
        $out.='<contenuto>';
        if(array_key_exists('id', $par) && $par['id'] != '') 
            {
            $out.='<idreq>'.$par['id'].'</idreq>';
            }
        if(array_key_exists('cf', $par) && $par['cf'] != '') 
            {
            $out.='<cfreq>'.$par['cf'].'</cfreq>';
            }
        if(array_key_exists('rid', $par) && $par['rid'] != '')
            {
            $out.='<ridreq>'.$par['rid'].'</ridreq>';
            }
        $out.='</contenuto>
</serequest>';
        return($this->send($out, 'a'));
        }

//    public function qryBadges($id, $startDate, $endDate)
//        {
//        $out=$this->header.$this->buildAction("d");
//        $out.='<contenuto>';
//        $out.='<idreq>'.$id.'</idreq>';
//        $out.='<stadata>'.$startDate.'</stadata>';
//        $out.='<stodata>'.$endDate.'</stodata>';
//        $out.='</contenuto>
//</serequest>';
//        return($this->send($out, 'r'));
//        }

    public function qryPres($aid, $dateTime)
        {
        $out=$this->header.$this->buildAction("p");
        $out.='<contenuto>';
        $out.='<aidreq>'.$aid.'</aidreq>';
        $out.='<timereq>'.$dateTime.'</timereq>';
        $out.='</contenuto>
</serequest>';
        return($this->send($out, 'r'));
        }

    public function qryRep($id, $startDate, $endDate)
        {
        $out=$this->header.$this->buildAction("d");
        $out.='<contenuto>';
        $out.='<idreq>'.$id.'</idreq>';
        $out.='<stadata>'.$startDate.'</stadata>';
        $out.='<stodata>'.$endDate.'</stodata>';
        $out.='</contenuto>
</serequest>';
        return($this->send($out, 'r'));
        }

    public function setBadge($badge, $par=array())
        {
        $out=$this->header.$this->buildAction("b");
        $out.='<contenuto>';
        if(array_key_exists('id', $par) && $par['id'] != '') 
            {
            $out.='<id>'.$par['id'].'</id>';
            }
        if(array_key_exists('cf', $par) && $par['cf'] != '') 
            {
            $out.='<codicefiscale>'.$par['cf'].'</codicefiscale>';
            }
        if(array_key_exists('rid', $par) && $par['rid'] != '')
            {
            $out.='<coll_id_se>'.$par['rid'].'</coll_id_se>';
            }
        $out.='<badgeId>'.$badge.'</badgeId>
           <force>true</force> 
      </contenuto>
</serequest>';
        return($this->send($out, 'a'));
        }

    public function setStatus($status, $par=array())
        {
        $out=$this->header.$this->buildAction("u");
        $out.='<contenuto>';
        if(array_key_exists('id', $par) && $par['id'] != '') 
            {
            $out.='<id>'.$par['id'].'</id>';
            }
        if(array_key_exists('cf', $par) && $par['cf'] != '') 
            {
            $out.='<codicefiscale>'.$par['cf'].'</codicefiscale>';
            }
        if(array_key_exists('rid', $par) && $par['rid'] != '')
            {
            $out.='<coll_id_se>'.$par['rid'].'</coll_id_se>';
            }
        $out.='<stato>'.$status.'</stato>
      </contenuto>
</serequest>';
        return($this->send($out, 'a'));
        }

    public function modLav()
        {
        // action body
        }

    }
    
    
//    public function testAction()
//        {
//        $xmlRead = <<<EOF
//<?xml version="1.0" encoding="UTF-8">
//<!--Document created by: Italo -->
//<serequest>
//    <azione>
//        <lname>guest</lname>
//        <lpa>D@mi@n0</lpa>
//        <myact>r</myact>
//    </azione>
//    <contenuto>
//        <idreq>101</idreq>
//    </contenuto>
//</serequest>        
//EOF;
//        //        <cfreq></cfreq>
//        //        <ridreq></ridreq>
//        $xmlWrite = <<<EOF
//<?xml version="1.0" encoding="UTF-8">
//<!--Document created by: Italo -->
//<serequest>
//    <azione>
//        <lname>guest</lname>
//        <lpa>D@mi@n0</lpa>
//        <myact>w</myact>
//    </azione>
//    <contenuto>
//        <nome>Italo</nome>
//        <cognome>Albanese</cognome>
//        <codicefiscale>lbntlf60e24f158n</codicefiscale>
//        <data_nascita>24/05/1960</data_nascita>
//        <stato>1</stato>
//        <coll_id_se>101</coll_id_se>
//    </contenuto>
//</serequest>        
//EOF;
//        //         <idreq>1</idreq>
//        $defaultNamespace = new Zend_Session_Namespace('Default');
//        $request = $this->getRequest();
//        $currAction = $request->getParam('act');
//        if ($currAction == "read")
//            {
//            $xml = $xmlRead;
//            }
//        elseif ($currAction == "write")
//            {
//            $xml = $xmlWrite;
//            }
//        else
//            {
//            return;
//            }
//        $client = new Zend_Http_Client('http://marcatempo.sipracloud.it/api/manage_user.ashx');
//        $client->setAdapter("Zend_Http_Client_Adapter_Curl");
//        $client->setHeaders('User-Agent', "MyUserAgent/1.1");
//        try
//            {
//            $client->setRawData($xml, 'text/xml');
//            $response = $client->request('POST');
//            }
//        catch (Exception $e)
//            {
//            $response = "Error: " . var_export($e, true);
//            }
//        $this->view->response = $response;
//        }
    