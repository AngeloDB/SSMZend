<?php

class Plugin_Manutenzione extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $request->setActionName('index');
        $request->setModuleName('default');
        $request->setControllerName('manutenzione');
    } 
}