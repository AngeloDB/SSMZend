<?php

class Plugin_Auth extends Zend_Controller_Plugin_Abstract
    {
    private $_identity;

    public function preDispatch(Zend_Controller_Request_Abstract $request)
        {
        if (!Zend_Auth::getInstance()->hasIdentity())
            {
            $request->setControllerName('login');
            $request->setActionName('index');
            }
        else
            {
            $defaultNamespace = new Zend_Session_Namespace('Default');
        
            $currUser = $defaultNamespace->user;
            $currUserLevel = $defaultNamespace->area;
            $view = Zend_Layout::getMvcInstance()->getView();
            $active = $view->navigation()->getContainer();
            $activePage =  $active->getPages();

            $currReqController = $request->getControllerName();
            $currReqAction = $request->getActionName();
            if($currReqController == "uploads")
                {
                if (!file_exists($currReqController.'/'.$currReqAction))
                    {
                    $request->setControllerName('service');
                    $request->setActionName('nofile');
                    $request->setParam('file', $currReqAction);
                    }
                return;
                }
            $currReqParameters = $request->getParams();

                $currAcl = $this->readAcl();
                $defaultNamespace->currAcl = $currAcl;
        
            if(is_null($defaultNamespace->currAcl))
                {
                $currAcl = $this->readAcl();
                $defaultNamespace->currAcl = $currAcl;
                }
            else
                {
                $currAcl = $defaultNamespace->currAcl;                
                }    
            
            if(is_array($currAcl) &&
                    array_key_exists('resources', 
                            $currAcl) &&
                    is_array($currAcl['resources']) &&
                    array_key_exists($currReqController, 
                            $currAcl['resources']) &&
                    is_array($currAcl['resources'][$currReqController]) &&
                    array_key_exists($currReqAction, 
                            $currAcl['resources'][$currReqController]) &&    
                    is_array($currAcl['resources'][$currReqController][$currReqAction]))    
                {
                $currPerm = $currAcl['resources'][$currReqController][$currReqAction];
                if(array_key_exists($currUserLevel, $currPerm))
                    {
                    $permission = $currPerm[$currUserLevel];
                    }
                elseif(array_key_exists('profile', $currPerm) && 
                       is_array($currAcl['profiles']) && 
                        array_key_exists($currUserLevel, $currAcl['profiles'][$currPerm['profile']]))
                    {
                    $currProfile = $currPerm['profile'];
                    $permission = $currAcl['profiles'][$currProfile][$currUserLevel];
                    }
                elseif (array_key_exists('All', $currPerm))
                    {
                    $permission = $currPerm['All'];
                    }    
                else    
                    {
                    $permission = "deny";
                    }
                }
            else    
                {
                $permission = "deny";
                }
                
            switch ($permission)
                {
                case "allow":
                    $defaultNamespace->titleTest = '';
                    $defaultNamespace->aclReadonly = '';
                    break;

                case "view":
                    $defaultNamespace->titleTest = 'Readonly!';
                    $defaultNamespace->aclReadonly = '1';
                    break;

                case "deny":
                    $request->setControllerName('service');
                    $request->setActionName('access-denied');
                    $defaultNamespace->noAllow = 'Non si entra!';
                    $defaultNamespace->noAllowController = $currReqController;
                    $defaultNamespace->noAllowAction = $currReqAction;
                    $defaultNamespace->noAllowParameters = $currReqParameters;
                    break;

                default:
                    break;
                }    
            
            }    
    
        }
        
        
    private function readAcl()
        {
        $configPath = APPLICATION_PATH . '/configs/acl.xml';
        $frontend = array(
            'master_files' => array(
                $configPath),
            'automatic_serialization' => true);
        $backend = array();
        $cache = Zend_Cache::factory('File', 'Memcached', $frontend, $backend);
        if (! ($config = $cache->load('configXml'))) 
            {
            $config = new Zend_Config_Xml($configPath, 'acl');
            $cache->save($config, 'configXml');
            }
        $acl = $config->toArray();
        $this->gridActive($acl);
        return $acl;
        }
        
    private function gridActive($acl)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $authGrid = array();
        foreach($acl['resources']['giorni-lav']['grid'] as $userLevel => $auth)
            {
            if($userLevel == 'profile')
                {
                foreach($acl['profiles'][$auth] as $ul2 => $a2)
                    {
                    if($a2 == 'allow')
                        {
                        $authGrid[] = $ul2;
                        }                
                    }
                }
            elseif($auth == 'allow')
                {
                $authGrid[] = $userLevel;
                }
            }
        $authGridEff = array();
        foreach($acl['resources']['giorni-lav']['grid-eff'] as $userLevel => $auth)
            {
            if($userLevel == 'profile')
                {
                foreach($acl['profiles'][$auth] as $ul2 => $a2)
                    {
                    if($a2 == 'allow')
                        {
                        $authGridEff[] = $ul2;
                        }                
                    }
                }
            elseif($auth == 'allow')
                {
                $authGridEff[] = $userLevel;
                }
            }
        $authGridSpese = array();
        foreach($acl['resources']['giorni-lav']['grid-spese'] as $userLevel => $auth)
            {
            if($userLevel == 'profile')
                {
                foreach($acl['profiles'][$auth] as $ul2 => $a2)
                    {
                    if($a2 == 'allow')
                        {
                        $authGridSpese[] = $ul2;
                        }                
                    }
                }
            elseif($auth == 'allow')
                {
                $authGridSpese[] = $userLevel;
                }
            }
        $defaultNamespace->allowedGrid = '"'.implode('","', $authGrid).'"';
        $defaultNamespace->allowedGridEff = '"'.implode('","', $authGridEff).'"';
        $defaultNamespace->allowedGridSpese = '"'.implode('","', $authGridSpese).'"';
        }

    }
