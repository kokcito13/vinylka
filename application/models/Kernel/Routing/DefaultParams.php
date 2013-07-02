<?php
class Application_Model_Kernel_Routing_DefaultParams {
	
	private $_params;
	
	public function __construct($serializedParams = 'a:0:{}') {
		$this->_params = (object)unserialize($serializedParams);
	}
	
	public function __set($param, $val) {
		$this->_params->{$param} = $val;
	}
	
	public function __get($param) {
		return $this->_params->{$param};
	}
	
	public function getArray() {
		return (array)$this->_params;	
	}
	
	public function __toString() {
		return serialize((array)$this->_params);
	}
}