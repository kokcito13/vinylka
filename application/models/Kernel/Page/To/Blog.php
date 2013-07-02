<?php
class Application_Model_Kernel_Page_To_Blog {
	
	const TYPE_INTERVIEW = 1;
	const TYPE_REVIEW = 2;
	
	private $_idPageToBlog;
	private $_idBlog;
	private $_idPage;
	private $_type;
	
	const ERROR_NOT_FOUND = 'ID PAGE IN PAGE TO BLOG NOT FOUND';
	const ERROR_TYPE = 'WRONG PAGE TO BLOG TYPE';
	
	public function __construct($idPageToBlog, $idBlog, $idPage, $pageToBlogType) {
		$this->_idPageToBlog = $idPageToBlog;
		$this->_idBlog = $idBlog;
		$this->_idPage = $idPage;
		$this->_type = $pageToBlogType;
	}

	public function getId() {
		return $this->_idPageToBlog;
	}
	
	public function setIdPage($idPage) {
		$this->_idPage = intval($idPage);
	}
	
	public function getIdPage() {
		return $this->_idPage;
	}
	
	public function setIdBlog($idBlog) {
		$this->_idBlog = intval($idBlog);
	}
	
	public function getIdBlog() {
		return $this->_idBlog;
	}

	public static function validType($type) {
		return in_array($type, array(
			self::TYPE_INTERVIEW,
			self::TYPE_REVIEW
		));
	}
	
	public function setType($type) {
		if (self::validType($type))
			$this->_type = intval($type);
		else
			throw new Exception(self::ERROR_TYPE);
	}
	
	public function getType() {
		return $this->_type;
	}
	
	/**
	 * @name save
	 * @access public
	 * @return void 
	 */
	public function save() {
		$db = Zend_Registry::get('db');
		$data = array(
			'idPage' => $this->_idPage,
			'idBlog' => $this->_idBlog,
			'pageToBlogType' => $this->_type
		);
		if (is_null($this->_idPageToBlog)) {
			$db->insert('pageToBlog', $data);
			$this->_idPageToBlog = $db->lastInsertId();
		} else {
			$db->update('pageToBlog', $data, 'idPageToBlog = ' . $this->getId());
		}
	}
	
	/**
	 * @name getById
	 * @access public
	 * @param int $idPageToBlog
	 * @throws Exception
	 * @return Application_Model_Kernel_Page_To_Blog
	 */
	public static function getById($idPageToBlog) {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from('pageToBlog');
		$select->where('idPageToBlog = ?', intval($idPageToBlog));
		$select->limit(1);
		if (($result = $db->fetchRow($select)) !== false)
			return new self($result->idPageToBlog, $result->idBlog, $result->idPage, $result->pageToBlogType);
		else
			throw new Exception(self::ERROR_NOT_FOUND);
	}

	/**
	 * @name getByIdPageAndType
	 * @access public
	 * @param int $idPage
	 * @param int $type
	 * @throws Exception
	 * @return Application_Model_Kernel_Page_To_Blog
	 */
	public static function getByIdPageAndType($idPage, $type) {
		if (!self::validType($type)) {
			throw new Exception(self::ERROR_TYPE);
		}
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from('pageToBlog');
		$select->where('idPage = ?', intval($idPage));
		$select->where('pageToBlogType = ?', intval($type));
		$select->limit(1);
		if (($result = $db->fetchRow($select)) !== false)
			return new self($result->idPageToBlog, $result->idBlog, $result->idPage, $result->pageToBlogType);
		else
			throw new Exception(self::ERROR_NOT_FOUND);
	}

	/**
	 * @param int $idPage
	 * @throws Exception
	 * @return Application_Model_Kernel_Page_To_Blog []
	 */
	public static function getAllToPage($idPage) {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from('pageToBlog');
		$select->where('idPage = ?', intval($idPage));
		$select->limit(1);
		if (($result = $db->fetchAll($select)) !== false) {
			$data = array();
			foreach ($result as $blogToPage) {
				$data[] = new self($blogToPage->idPageToBlog, $blogToPage->idBlog, $blogToPage->idPage, $blogToPage->pageToBlogType);
			}
			return $data;
		} else
			throw new Exception(self::ERROR_NOT_FOUND);
	}

}