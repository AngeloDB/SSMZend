<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
//        Zend_Dojo::enableView($this->view);
//        Zend_Dojo_View_Helper_Dojo::setUseDeclarative();
    }

    public function indexAction()
    {
//        $this->view->headLink()->appendStylesheet($this->view->baseUrl() .
//            '/styles/styles.css');
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();

        // $this->view->baseUrl() . 
        if ($userInfo == null)
            {
            $this->_redirect('/login');
            }
        else
            {
            $defaultNamespace = new Zend_Session_Namespace('Default');
            $currArea = $defaultNamespace->area;
            switch ($currArea)
                {
                case 'SuperIT':
                case 'SuperLU':
                    $this->_redirect('/index/admin');
                    break;

                case 'Superuser':
                    $this->_redirect('/index/superuser');
                    break;

                case 'PM-CM':
                case 'Project':
                case 'Guest':
                case 'Admin':
                case 'Secretary':
                case 'Info':
                case 'User':
                    $this->_redirect('/index/admin');
                    break;

                default:
                    break;
                }
            }
        //
    }

    public function guestAction()
    {
        $this->view->title = 'Guest';
    }

    public function superuserAction()
    {
        $this->view->title = 'Superuser';
    }

    public function adminAction()
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $request = $this->getRequest();
        $country = strtoupper($request->getParam('id'));
        if($country == '')
            {
            $country = 'WW';
            }
        $selMap = $this->db->select()->from(array('A' => 'mappe'), 
                array('idMappa', 'Sigla', 'Mappa', 'File', 'Nome', 
                    'MapHeight', 'MapWidth', 'Scala', 'DotSize',
                    'LatN', 'LatS', 'LongW', 'LongE', 'Note'
                    ))->where('Sigla = ?', $country);
        $maps = $selMap->query();
        $resMappa = $maps->fetchAll();

        if (!$resMappa)
            {
            $mappa = array(
                'idMappa' => 0, 
                'Sigla'   => 'WW', 
                'Mappa'   => 'world_countries', 
                'File'    => 'world/world_countries.js', 
                'Nome'    => 'World sites', 
                'MapHeight' => 0, 
                'MapWidth' => 0, 
                'Width'   => 0,
                'Scala'   => '', 
                'DotSize' => 7, 
                'LatN'    => 90, 
                'LatS'    => -90, 
                'LongW'   => 180, 
                'LongE'   => -180, 
                'Note'    => ''                 
                );
            }
        else
            {
            $mappa = $resMappa[0];
            }    
        $defaultNamespace->cant2 = $mappa['Nome'];
        $currMap = $mappa['Mappa'];
        $currUser = $defaultNamespace->user;
        $this->view->currUser = $currUser;
        $this->view->title = 'Amministratore';
        $select1 = $this->db->select()->from(array('A' => 'locations'), 
                array('idProgetto', 
                    'Nome', 
                    'Latitudine', 
                    'Longitudine',
                    'Status' => '("0")'));
        $select2 = $this->db->select()->from(array('A' => 'progetti'), 
                array('idProgetto', 
                    'Nome', 
                    'Latitudine', 
                    'Longitudine',
                    'Status' => '(CASE WHEN A.OnHold = 1 THEN "0" '.
                                     'WHEN A.Inizio > NOW() THEN "2" '.
                                     'WHEN A.Fine < NOW() THEN "1" '.
                                'ELSE "3" END)'));
//        $select2 = $this->db->select()->from(array('A' => 'progetti'), 
//                array('idProgetto', 'Nome', 'Latitudine', 'Longitudine'
//                    ))->where('idProgetto in (20,12,17,58,64,32,62,52,39,37,1,25)');
        $sel = $this->db->select();
        $sel->union(array($select1, $select2));
        $smt = $sel->query();
        $locations = $smt->fetchAll();
        $this->view->locations = $locations;
        $this->view->mappa = $mappa;
        $this->view->currMap = $mappa['Mappa'];
        $this->view->scale = $mappa['Scala'];
        $this->view->sigla = $mappa['Sigla'];
//        $this->view->width = $mappa['Width'];
        $this->view->dotSize = $mappa['DotSize'];
        if($mappa['MapWidth'] > 0 && $mappa['MapHeight'] > 0) { 
            $this->view->mapRatio = $mappa['MapWidth'] / $mappa['MapHeight'];
            }
        else {
            $this->view->mapRatio = 0;
            }                        
        
        $attr1 = 'text/javascript';
        $attr2 = array('charset' => 'utf-8');
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-mapael/js/maps/'.$mappa['File'], $attr1, $attr2);
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-mapael/js/jquery.mapael.js', $attr1, $attr2);
        $this->view->headScript()->prependFile($this->view->baseUrl() .
                '/jq-raphael/raphael.min.js', $attr1, $attr2);
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() .
                '/styles/personale_riass.css');
    }

    
    
//    
//    SELECT SQL_CALC_FOUND_ROWS
//    1 +1 AS `ZFG_GHOST`,
//    `progetti`.`idProgetto`,
//    `progetti`.`Nome`,
//    `progetti`.`JobNumber`,
//    `progetti`.`Paese`,
//    `progetti`.`Indirizzo`,
//    `progetti`.`Inizio`,
//    `progetti`.`Fine`,
//    (
//        CASE WHEN progetti.Inizio > NOW() THEN "doc_status_8.png" WHEN progetti.Fine < NOW() THEN "doc_status_2.png" ELSE "doc_status_1.png"
//        END) AS `SemImg`,
//        (
//            CASE WHEN progetti.Inizio > NOW() THEN "2" WHEN progetti.Fine < NOW() THEN "1" ELSE "3"
//            END) AS `SemOrder`,
//            `progetti`.`ContractValue`,
//            `spesemese`.`idSpeseMese`,
//            `prog_steps`.`idProgStep`,
//            `richieste`.`idRichiesta`,
//            `giorni_lav`.`idGiorniLav`,
//            (
//                CASE WHEN SUM(giorni_lav.GiorniEff) > 0 OR MIN(spesemese.idSpeseMese) IS NOT NULL THEN 'del-dis' ELSE 'fechar'
//            END
//        ) AS `delIco`,
//        (
//            CASE WHEN SUM(giorni_lav.GiorniEff) > 0 OR MIN(spesemese.idSpeseMese) IS NOT NULL THEN '#' ELSE CONCAT(
//                '/progetti/delete/id/',
//                progetti.idProgetto
//            )
//        END
//    ) AS `delLink`,
//    (
//        CASE WHEN SUM(giorni_lav.GiorniEff) > 0 OR MIN(spesemese.idSpeseMese) IS NOT NULL THEN 'alert(''Can`t delete''); return false;' ELSE 'return(confirm(''Confirm delete?''));'
//    END
//) AS `delAlert`,
//`business_units`.`Descrizione` AS `BU`,
//`contratti`.`Sigla` AS `Contractor`,
//CONCAT(utenti.nome, " ", utenti.cognome) AS `HqCoord`
//FROM
//    `progetti`
//LEFT JOIN `spesemese` ON spesemese.idProgetto = progetti.idProgetto
//LEFT JOIN `prog_steps` ON prog_steps.idProgetto = progetti.idProgetto
//LEFT JOIN `richieste` ON richieste.idProgStep = prog_steps.idProgStep
//LEFT JOIN `giorni_lav` ON giorni_lav.idRichiesta = richieste.idRichiesta
//LEFT JOIN `business_units` ON business_units.idBusinessUnit = progetti.idBusinessUnit
//LEFT JOIN `contratti` ON contratti.idContratto = progetti.PWMainContractor
//LEFT JOIN `utenti` ON utenti.id_utente = progetti.HQ_Coordinator
//WHERE
//    (
//        ("SuperIT" != "PM-CM") OR(
//            "SuperIT" = "PM-CM" AND "" LIKE CONCAT("%#", progetti.idProgetto, "#%")
//        )
//    )
//GROUP BY
//    `progetti`.`idProgetto`
//ORDER BY
//    CONCAT(SemOrder, JobNumber +0, JobNumber)
//DESC
//LIMIT
//    
//    
//    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
//    public function adminAction()
//    {
//        $defaultNamespace = new Zend_Session_Namespace('Default');
//        $request = $this->getRequest();
//        $country = $request->getParam('id');
//        if($country == '')
//            {
//            $country = 'WW';
//            }
//        $defaultNamespace->cant2 = "World sites";
//        $currMap = 'world_countries';
//        $currUser = $defaultNamespace->user;
//        $this->view->currUser = $currUser;
//        $this->view->title = 'Amministratore';
//        $select1 = $this->db->select()->from(array('A' => 'locations'), 
//                array('idProgetto', 'Nome', 'Latitudine', 'Longitudine'));
//        $select2 = $this->db->select()->from(array('A' => 'progetti'), 
//                array('idProgetto', 'Nome', 'Latitudine', 'Longitudine'));
//        $sel = $this->db->select();
//        $sel->union(array($select1, $select2));
//        $smt = $sel->query();
//        $locations = $smt->fetchAll();
//        $this->view->locations = $locations;
////        $this->view->currMap = 'india_states';
//        $this->view->currMap = 'world_countries';
////        $this->view->currMap = 'russia';
//        $this->view->scale = '';
//        $this->view->dotSize = '7';
//        $attr1 = 'text/javascript';
//        $attr2 = array('charset' => 'utf-8');
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/jq-mapael/js/maps/world/world_countries.js', $attr1, $attr2);
////                '/jq-mapael/js/maps/russia/russia.js', $attr1, $attr2);
////                '/jq-mapael/js/maps/india/india_states.js', $attr1, $attr2);
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/jq-mapael/js/jquery.mapael.js', $attr1, $attr2);
//        $this->view->headScript()->prependFile($this->view->baseUrl() .
//                '/jq-raphael/raphael.min.js', $attr1, $attr2);
//    }
//
}


