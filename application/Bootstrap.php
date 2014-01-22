<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initTimezone()
    {
        date_default_timezone_set('Europe/Kiev');
    }

    protected function _initConfiguration()
    {
        Zend_Registry::set('cnf', $this->getOptions());
    }

    protected function _initCache()
    {
        $this->_executeResource('cachemanager');
        Zend_Registry::set('cachemanager', $this->getResource('cachemanager'));
    }

    protected function _initDatabase()
    {
        $this->_executeResource('db');
        Zend_Registry::set('db', $this->getResource('db'));
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
    }

    protected function _initRouting()
    {
        $front  = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();
        //$cachemanager = Zend_Registry::get('cachemanager');
        //$cache = $cachemanager->getCache('routes');
        //if ($list = $cache->load('ROUTES'))
        //$router->addRoutes($list);
        //else {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', 'production');
        $router->addConfig($config, 'routes');
        $dbRoute = Application_Model_Kernel_Routing::getRoutingList();
        Application_Model_Kernel_Routing_Manager::setListToRouter($dbRoute, $router);
        //$cache->save($router->getRoutes());
        //}
    }

    protected function _initView()
    {
        Zend_Session::start();

        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $view->headTitle()->setSeparator(' | ');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8');
        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($view->blocks as $key => $value) {
            $view->blocks[$key] = $value->getContent()->getFields();
        }
        $view->siteSetings = Application_Model_Kernel_SiteSetings::getBy();

        $remarketing      = new Zend_Session_Namespace('remarketing');
        if (!isset($remarketing->product_id)) {
            $remarketing->product_id = 0;
        }
        $view->product_id = $remarketing->product_id;

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);
    }

    protected function _initMenuCookie()
    {
        if (!isset($_COOKIE['menu'])) {
            setcookie('menu', 0, time() + 2592000, '/');
            $_COOKIE['menu'] = 0;
        }
    }

    protected function _initZFDebug()
    {
        if (isset($_GET['DBG'])) {
            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloader->registerNamespace('ZFDebug');
            $options = array (
                'plugins' => array (
                    'Variables',
                    'Database' => array ('adapter' => Zend_Registry::get('db')),
                    'File'     => array ('basePath' => '/path/to/project'),
                    'Memory',
                    'Time',
                    'Registry',
                    'Exception',
                    'Exception'
                )
            );
            $debug   = new ZFDebug_Controller_Plugin_Debug($options);
            $this->bootstrap('frontController');
            $frontController = $this->getResource('frontController');
            $frontController->registerPlugin($debug);
        }
    }

}
