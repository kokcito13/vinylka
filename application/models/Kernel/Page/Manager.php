<?php
class Application_Model_Kernel_Page_Manager {
	
	public static function getLastRouteId() {
		$db = Zend_Registry::get('db');	
		$select = $db->query("SHOW TABLE STATUS LIKE 'pages'");
		return $select->fetch()->Auto_increment;
	}
	
}