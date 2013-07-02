<?php
class Application_Model_Kernel_Aphorism {
	
	protected $idAphorism;
	protected $idContentPack;
	protected $aphorismStatus;
    protected $aphorismPosition;


    /**
	 * @var Application_Model_Kernel_Content_Manager
	 */
	private  $_contentManager = null;
	/**
	 * @var Application_Model_Kernel_Content_Lang
	 */
	private $_content = null;
	
	
	const STATUS_SHOW = 1;
	const STATUS_HIDE = 0;
		
	public function __construct($idAphorism, $idContentPack, $aphorismStatus, $aphorismPosition) {
		$this->idAphorism = $idAphorism;
		$this->idContentPack = $idContentPack;
		$this->aphorismStatus = $aphorismStatus;
        $this->aphorismPosition = $aphorismPosition;
	}
	
	
	/**
	 * @name getById
	 * @param int $idAphorism
	 * @throws Exception
	 * @return Application_Model_Kernel_Category
	 */
	public static function getById($idAphorism) {
		$idAphorism = (int)$idAphorism;
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('aphorisms');
		$select->where('aphorisms.idAphorism = ?', $idAphorism);
		if (false !== ($data = $db->fetchRow($select))) {
			return new self($data->idAphorism, $data->idContentPack, $data->aphorismStatus, $data->aphorismPosition);
		} else
			throw new Exception(self::TYPE_ERROR_ID_NOT_FOUND);
	}
	
	public function getId() {
		return $this->idAphorism;
	}
	
	
	
	public function getStatus() {
		return $this->aphorismStatus;
	}
	
	public function getPosition() {
		return $this->aphorismPosition;
	}
	
	public function setPosition($position) {
		$this->aphorismPosition = intval($position);
	}
	
	
	/**
	 * @param Application_Model_Kernel_Content_Manager $contentManager
	 * @throws Exception ERROR_CONTENT_MANAGER_GIVEN
	 * @return void
	 */
	public function setContentManager(Application_Model_Kernel_Content_Manager &$contentManager) {
		$this->_contentManager = $contentManager;
	}
	
	/**
	 * @return Application_Model_Kernel_Content_Manager
	 */
	public function getContentManager() {
		if (is_null($this->_contentManager)) {
			$this->_contentManager = Application_Model_Kernel_Content_Manager::getById($this->idContentPack);
		}
		return $this->_contentManager;
	}
	
	/**
	 * @return Application_Model_Kernel_Content_Lang
	 */
	public function getContent() {
		if (is_null($this->_content)) {
			$this->_content = Application_Model_Kernel_Content_Language::get($this->idContentPack, Kernel_Language::getCurrent()->getId());
		}
		return $this->_content;
	}
	
	/**
	 * @param Application_Model_Kernel_Content_Lang $contentLang
	 * @return void
	 */
	public function setContent(Application_Model_Kernel_Content_Language &$contentLang) {
		$this->_content = $contentLang;
	}

	
	public function validate() {
		$e = new Application_Model_Kernel_Exception();
		if (is_null($this->_contentManager)) {
			throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
		}
		$this->_contentManager->validate($e);
		if((bool)$e->current()) 
			throw $e;
	}
	
	
	public static function getSelf($data){
		return new self($data->idAphorism, $data->idContentPack, $data->aphorismStatus, $data->aphorismPosition);
	}
	public static function getList($status = false) {
		$db = Zend_Registry::get('db');
		$return = array();
		$select = $db->select()->from('aphorisms');
        if( $status != false )
            $select->where('aphorisms.aphorismStatus = '.self::STATUS_SHOW);
        $select->order(' aphorisms.aphorismPosition DESC ');
		if (false !== ($result = $db->fetchAll($select))) {
			foreach ($result as $category) {
				$return[] = self::getSelf($category);
			}
		}
		return $return;
	}
	
	public function save() {
		$data = array(
			'idContentPack' => $this->idContentPack,
			'aphorismStatus' => $this->aphorismStatus,
			'aphorismPosition' => $this->aphorismPosition
		);
		$db = Zend_Registry::get('db');
		if (is_null($this->idAphorism)) {
			if (is_null($this->_contentManager))
				throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
				$this->_contentManager->saveContentData();//Сохраняем весь конент через меджер
			$this->idContentPack = $this->_contentManager->getIdContentPack();//ставим AI idContent
			$data['idContentPack'] = $this->idContentPack;
			$db->insert('aphorisms', $data);
			$this->idAphorism = $db->lastInsertId();
		} else {
			$this->getContentManager()->saveContentData();
			$db->update('aphorisms', $data, 'idAphorism = ' . intval($this->idAphorism));
		}
	}

	public function show() {
		$db = Zend_Registry::get('db');
		$db->update('aphorisms',array(
			'aphorisms.aphorismStatus' => self::STATUS_SHOW
		),"aphorisms.idAphorism = {$this->idAphorism}");
	}
	
	public function hide() {
		$db = Zend_Registry::get('db');
		$db->update('aphorisms',array(
			'aphorisms.aphorismStatus' => self::STATUS_HIDE
		),"aphorisms.idAphorism = {$this->idAphorism}");
	}
	
	public function delete() {
		$db = Zend_Registry::get('db');
		$db->delete('aphorisms',"aphorisms.idAphorism = {$this->idAphorism}");
		$this->getContentManager()->delete();
	}
	public static function changePosition($id, $position) {
        $db = Zend_Registry::get('db');
        $db->update('aphorisms', array("aphorismPosition" => $position), 'aphorisms.idAphorism = ' . (int) $id);

        for ($i = 0; $i < 2000; $i++) {
            
        }
        return true;
    }
}