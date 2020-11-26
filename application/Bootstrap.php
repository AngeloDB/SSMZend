<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
    {

    private $_acl = null;
    private $_auth = null;

    protected function _initAutoload()
        {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => APPLICATION_PATH));
        $moduleLoader->addResourceType('grid', 'grids/', 'Grid');
//        $moduleLoader->addResourceType('PHPExcel', 'PHPExcel/', 'PHPExcel');

        return $moduleLoader;
        }

    protected function _initDb()
        {
//        var_dump( array('CodeIgniter', 'php', 'phpMyAdmin', 'lucidar.me') );
//        die;
        $resource = $this->getPluginResource('db');
        $db = $resource->getDbAdapter();
        Zend_Registry::set("db", $db);
        $conf = $this->getOption('resources');
        if($conf['db']['adapter'] == "pdo_mysql")
            {
            $db->query('SET SESSION group_concat_max_len=15000');
            }
        return $db;
        }

    protected function _initConfig()
        {
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
        $frontend = array('lifetime' => 345600, 'automatic_seralization' => true);
        $backend = array('cache_dir' => APPLICATION_PATH . '/cache/');
        $cache = Zend_Cache::factory('Core', 'File', $frontend, $backend);
        $cache->setOption('automatic_serialization', true);
        Zend_Registry::set('cache', $cache);
        $locale = new Zend_Locale('it_IT');
        $locale->setLocale('it_IT');
        Zend_Locale::setDefault('it_IT');
        Zend_Registry::set('Zend_Locale', $locale);
        Zend_Locale::setCache($cache);
        return $config;
        }

    protected function _initTranslate()
        {
        $translate = new Zend_Translate(array(
            'adapter' => 'csv',
            'content' => APPLICATION_PATH . '/configs/languages/en.csv',
            'locale'  => 'en',
            'delimiter' => ','
        ));

        Zend_Registry::set('translate', $translate);
        Zend_Form::setDefaultTranslator($translate);
        Zend_Validate_Abstract::setDefaultTranslator($translate);

        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->translate = $translate;
        }

    protected function _initRequest()
        {
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
        $request = new Zend_Controller_Request_Http();
        $front->setRequest($request);
        return $request;
        }

    protected function _initViewHelpers()
        {
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();
//        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $view->headMeta()->appendHttpEquiv('Content-type', 'text/html;charset=utf-8')
                ->appendName('description', 'CrewPlan');

        $view->headTitle()->setSeparator(' - ');
        $view->headTitle('CrewPlan');

//        $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
//        $view->addHelperPath('My/Dojo/View/Helper/', 'My_Dojo_View_Helper');
//        $view->dojo()->setLocalPath('/js/dojo/dojo/dojo.js');
        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
                        'ViewRenderer');
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        return $view;
        }

    protected function _initNavigation()
        {
//        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();
//        $view = new Zend_View();
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $config = Zend_Registry::get('config');
        $localPath = $config->downloads->get('localPath');
        $defaultNamespace->cantiere = $config->cantiere->get('nome') . " " . $config->cantiere->get('sottotitolo');
        //"CrewPlan demo";
        if (isset($defaultNamespace->area))
            {
            $currArea = $defaultNamespace->area;
            if ($currArea == '')
                {
                $currArea = 'base';
                }
            }
        else
            {
            $currArea = 'base';
            $defaultNamespace->area = $currArea;
            }
        $configMenu = new Zend_Config_Xml(APPLICATION_PATH .
                '/configs/navigation.xml', $currArea);

        $currXml = $configMenu->toArray();
        $defaultNamespace->xmlmenu = (array) $currXml;

        $defaultNamespace->baseUrl = $view->baseUrl();
        $container = new Zend_Navigation($configMenu);
        $translate = Zend_Registry::get('translate');
        $view->navigation($container)->menu()->setUlClass('nav')
                                     ->setTranslator($translate);
        $view->navigation($container);
//        $view->headLink()->appendStylesheet($view->baseUrl() .
//                '/js/dojo/dijit/themes/tundra/tundra.css');
        return $view;
        }

    protected function _initBvb()
        {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Bvb_');
        $autoloader->registerNamespace('Custom_');
        $autoloader->registerNamespace('PHPExcel_');
//        $autoloader->registerNamespace('PHPExcel');
        $autoloader->registerNamespace('My_');
        $autoloader->registerNamespace('Acl_');
        $autoloader->registerNamespace('Mpdf_');
        $autoloader->registerNamespace('Dompdf_');
        $autoloader->registerNamespace('Gorilla_');
        }

    protected function _initPlugins()
        {
        $frontController = $this->getResource('frontController');
        $frontController->registerPlugin(new Plugin_Auth());
        }

    protected function _initZFDebug()
        {
        $config = new Zend_Config($this->getOptions());
        if ($config->debugBar->get('active'))
            {
            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloader->registerNamespace('ZFDebug');
            $db = Zend_Registry::get('db');
            //           'Cache' => array('backend' => $cache->getBackend()), 
            $options = array(
                'plugins' => array('Variables',
                    'Database' => array('adapter' => $db),
                    'File' => array('basePath' => '/path/to/project'),
                    'Memory',
                    'Time',
                    'Registry',
                    'Exception')
            );
            $debug = new ZFDebug_Controller_Plugin_Debug($options);

            $this->bootstrap('frontController');
            $frontController = $this->getResource('frontController');
            $frontController->registerPlugin($debug);
            }
        }

    }
