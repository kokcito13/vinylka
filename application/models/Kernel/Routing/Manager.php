<?php
class Application_Model_Kernel_Routing_Manager {
	
	public static function getLastRouteId() {
		$db = Zend_Registry::get('db');	
		$select = $db->query("SHOW TABLE STATUS LIKE 'routing'");
		return $select->fetch()->Auto_increment;
	}
	
	public static function setListToRouter(array $routeList, $router) {
		if (!empty($routeList)) {
            $langs = Kernel_Language::getAll();
			foreach ($routeList as $route) {//route -> Application_Model_Kernel_Routing
				switch($route->getType()) {
					case Application_Model_Kernel_Routing::TYPE_ROUTE:
                        foreach( $langs as $lang){ 
                            if( $lang->getIsoName() == Kernel_Language::DEFAULT_LANG ){
                                $router->addRoute($route->getName(), new Zend_Controller_Router_Route($route->getUrl(), $route->getParams()));
                            } else {
                                $router->addRoute($route->getName()."-".$lang->getCustomName(), new Zend_Controller_Router_Route("/".$lang->getCustomName().$route->getUrl(), $route->getParams()));
                            }
                        }
                    break;
					default:
						throw new exception(Application_Model_Kernel_Routing::ERROR_INVALID_ROUTE_TYPE);
					break;
				}
			}
		}
	}
	
	public static function clearCache() {
		$cacheManager = Zend_Registry::get('cachemanager');
    	$cacheManager->getCache('routes')->clean();
	}
}