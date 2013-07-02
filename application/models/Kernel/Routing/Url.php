<?php
class Application_Model_Kernel_Routing_Url {
	
	private $url;
	
	/**
	 * @access public
	 * @param string url
	 * @ParamType url string
	 */
	public function __construct($url = '') {
		$this->url = $this->stripLastSlashes($url);
	}
	
	public function checkUnique($excludedId = null) {
    	$db = Zend_Registry::get('db');
		$select = $db->select()->from('routing',array('count'=>'COUNT(idRoute)'));
		$select->where('url = ? ',$this->url);
		if (!is_null($excludedId))
			$select->where('idRoute != ? ',$excludedId);
		$res = $db->fetchRow($select);
		return (intval($res->count) === 0);
	}
	
	/**
	 * @access public
	 */
	private function stripLastSlashes($url) {
    	if (substr($url,-1) === '/' && $url !== '/') {
    		$url = substr($url,0,-1);
    		return (substr($url,-1) === '/') ? $this->stripLastSlashes($url) : $url;
    	} else
    		return $url;
	}
	
	public function __toString() {
		return $this->url;
	}
	
}