<?php
class Application_Model_Kernel_Routing {
	
	private $_idRoute;
	private $_type;
	private $_name;
	private $_module;
	private $_controller;
	private $_action;
	/**
	 * @var Application_Model_Kernel_Routing_Url
	 */
	private $_url;
	private $_status;
	/**
	 * @var Application_Model_Kernel_Routing_DefaultParams
	 */
	public $defaultParams;
	private $_oldUrl;
	protected $_view = null;
	
	const TYPE_ROUTE  = 1;
	const TYPE_STATIC = 2;
	const TYPE_REGEX = 3;
	
	const STATUS_ACTIVE = 1;
	const STATUS_DISABLED = 0;
	
	const ERROR_INVALID_ROUTE_ID = 'Invalid route id';
	const ERROR_INVALID_ROUTE_TYPE = 'Invalid route type';
	const ERROR_ALIAS = 'Wrong format of page alias';
	const ERROR_ALIAS_EXISTS = 'Such alias already exists';
	
	public function __construct($idRoute, $type, $name, $module, $controller, $action, Application_Model_Kernel_Routing_Url $url, Application_Model_Kernel_Routing_DefaultParams $defaultParams, $routeStatus) {
		$this->_idRoute = $idRoute;
		$this->_type = $type;
		$this->_name = $name;
		$this->_module = $module;
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_url = $url;
		$this->defaultParams = $defaultParams;
		$this->_routeStatus = $routeStatus;
	}

	public function getOldUrl() {
		return $this->_oldUrl;
	}
	
	/**
	 * @param int $idRoute
	 * @throws Exception
	 * @return Application_Model_Kernel_Routing
	 */
	public static function getById($idRoute) {
		$idRoute = intval($idRoute);
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('routing');
		$select->where('routing.idRoute = ?', $idRoute);
		$select->limit(1);
		if (false !== ($route = $db->fetchRow($select)))
			return new self($idRoute, $route->type, $route->name, $route->module, $route->controller, $route->action, new Application_Model_Kernel_Routing_Url($route->url), new Application_Model_Kernel_Routing_DefaultParams($route->defaultParams), $route->routeStatus);
		else
			throw new Exception(self::ERROR_INVALID_ROUTE_ID);
	}

	public function validate(Application_Model_Kernel_Exception &$e) {
		if (!preg_match('/^\/[a-z0-9\_\.\-\/]*\/?$/i', $this->_url )) {
			$e[] = self::ERROR_ALIAS;
		} else {
			if (!$this->_url->checkUnique($this->_idRoute))
				$e[] = self::ERROR_ALIAS_EXISTS;
		}
	}
	
	public function getId() {
		return $this->_idRoute;
	}
	
	public function getType() {
		return $this->_type;	
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getStatus() {
		return (int)$this->_routeStatus;
	}
	
	/**
	 * @param int $status
	 * @throws Exception
	 * @return Application_Model_Kernel_Routing
	 */
	public function setStatus($status) {
		if (in_array($status, array(
			self::STATUS_ACTIVE,
			self::STATUS_DISABLED
		))) {
			$this->_routeStatus = intval($status);
		} else
			throw new Exception('WRONG STATUS GIVEN');
		return $this;
	}
	
	public function setName($name) {
		$this->_name = $name;
	}
	
	public function setUrl($url) {
		$this->_oldUrl = $this->_url->__toString();
		unset($this->_url);
		$this->_url = new Application_Model_Kernel_Routing_Url($url);
	}
	
	public function getUrl() {
		return ($this->_url->__toString());
	}
	
	public function getParams() {
		return array_merge(array(
			'controller' => $this->_controller,
			'action' => $this->_action,
			'module' => $this->_module,
			'idRoute' => $this->_idRoute
		), $this->defaultParams->getArray());
	}
		
	public function save() {
		$data = array(
			'idRoute' => $this->_idRoute,
			'type' => $this->_type,
			'name' => $this->_name,
			'module' => $this->_module,
			'controller' => $this->_controller,
			'action' => $this->_action,
			'url' => $this->_url->__toString(),
			'defaultParams' => $this->defaultParams->__toString(),
			'routeStatus' => $this->_routeStatus
		);
		$db = Zend_Registry::get('db');
		if (is_null($this->_idRoute)) {
			$db->insert('routing', $data);
			$this->_idRoute = $db->lastInsertId();
		} else {
			$db->update('routing', $data, 'idRoute = ' . intval($this->_idRoute));
		}
		//Application_Model_Kernel_Routing_Manager::clearCache();
	}

	public function delete() {
		$db = Zend_Registry::get('db');
		$db->delete('routing', "idRoute = " . intval($this->_idRoute));
//		Application_Model_Kernel_Routing_Manager::clearCache();
	}
	
	public static function getRoutingList() {
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('routing');
		$select->where('routing.routeStatus = ?', self::STATUS_ACTIVE);
        $select->order('idRoute');
		$routingList = array();
		if (($result = $db->fetchAll($select)) !== false) {
			foreach ($result as $route) {
				$routingList[] = new Application_Model_Kernel_Routing($route->idRoute, $route->type, $route->name, $route->module, $route->controller, $route->action, new Application_Model_Kernel_Routing_Url( $route->url ), new Application_Model_Kernel_Routing_DefaultParams( $route->defaultParams ), $route->routeStatus);
			}
		}
		return $routingList;
	}

}