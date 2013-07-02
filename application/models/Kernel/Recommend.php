<?php
class Application_Model_Kernel_Recommend {
	
	protected $idRecommend;
	protected $idContentPack;
	protected $recommendStatus;
    protected $recommendPosition;

    private $idPhoto1;
    private $photo1 = null;

    private  $_contentManager = null;
	
    private $_content = null;
	
	
	const STATUS_SHOW = 1;
	const STATUS_HIDE = 0;
		
	public function __construct($idRecommend, $idPhoto1, $idContentPack, $recommendStatus, $recommendPosition) {
		$this->idRecommend = $idRecommend;
        $this->idPhoto1 = $idPhoto1;
		$this->idContentPack = $idContentPack;
		$this->recommendStatus = $recommendStatus;
        $this->recommendPosition = $recommendPosition;
	}
	
	
    public function getIdPhoto1() {
        return $this->idPhoto1;
    }

    public function getPhoto1() {
        if (is_null($this->photo1))
            $this->photo1 = Application_Model_Kernel_Photo::getById($this->idPhoto1);
        return $this->photo1;
    }

    public function setPhoto1(Application_Model_Kernel_Photo &$photo1) {
        $this->photo1 = $photo1;
        return $this;
    }

    public function setIdPhoto1($idPhoto1) {
        $this->idPhoto1 = $idPhoto1;
    }
    
	public static function getById($idRecommend) {
		$idRecommend = (int)$idRecommend;
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('recommends');
		$select->where('recommends.idRecommend = ?', $idRecommend);
		if (false !== ($data = $db->fetchRow($select))) {
			return self::getSelf($data);
		} else
			throw new Exception(self::TYPE_ERROR_ID_NOT_FOUND);
	}
	
	public function getId() {
		return $this->idRecommend;
	}
	
	public function getStatus() {
		return $this->recommendStatus;
	}
	
	public function getPosition() {
		return $this->recommendPosition;
	}
	
	public function setPosition($position) {
		$this->recommendPosition = intval($position);
	}
	
	
	public function setContentManager(Application_Model_Kernel_Content_Manager &$contentManager) {
		$this->_contentManager = $contentManager;
	}
	
	public function getContentManager() {
		if (is_null($this->_contentManager)) {
			$this->_contentManager = Application_Model_Kernel_Content_Manager::getById($this->idContentPack);
		}
		return $this->_contentManager;
	}
	
	public function getContent() {
		if (is_null($this->_content)) {
			$this->_content = Application_Model_Kernel_Content_Language::get($this->idContentPack, Kernel_Language::getCurrent()->getId());
		}
		return $this->_content;
	}
	
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
		return new self($data->idRecommend, $data->idPhoto1, $data->idContentPack, $data->recommendStatus, $data->recommendPosition);
	}
    
	public static function getList($status = false) {
		$db = Zend_Registry::get('db');
		$return = array();
		$select = $db->select()->from('recommends');
        if( $status != false )
            $select->where('recommends.recommendStatus = '.self::STATUS_SHOW);
        $select->order(' recommends.recommendPosition DESC ');
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
            'idPhoto1' => $this->idPhoto1,
			'recommendStatus' => $this->recommendStatus,
			'recommendPosition' => $this->recommendPosition
		);
		$db = Zend_Registry::get('db');
		if (is_null($this->idRecommend)) {
			if (is_null($this->_contentManager))
				throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
				$this->_contentManager->saveContentData();//Сохраняем весь конент через меджер
			$this->idContentPack = $this->_contentManager->getIdContentPack();//ставим AI idContent
			$data['idContentPack'] = $this->idContentPack;
			$db->insert('recommends', $data);
			$this->idRecommend = $db->lastInsertId();
		} else {
			$this->getContentManager()->saveContentData();
			$db->update('recommends', $data, 'idRecommend = ' . intval($this->idRecommend));
		}
	}

	public function show() {
		$db = Zend_Registry::get('db');
		$db->update('recommends',array(
			'recommends.recommendStatus' => self::STATUS_SHOW
		),"recommends.idRecommend = {$this->idRecommend}");
	}
	
	public function hide() {
		$db = Zend_Registry::get('db');
		$db->update('recommends',array(
			'recommends.recommendStatus' => self::STATUS_HIDE
		),"recommends.idRecommend = {$this->idRecommend}");
	}
	
	public function delete() {
		$db = Zend_Registry::get('db');
		$db->delete('recommends',"recommends.idRecommend = {$this->idRecommend}");
		$this->getContentManager()->delete();
	}
	public static function changePosition($id, $position) {
        $db = Zend_Registry::get('db');
        $db->update('recommends', array("recommendPosition" => $position), 'recommends.idRecommend = ' . (int) $id);

        for ($i = 0; $i < 2000; $i++) {
            
        }
        return true;
    }
}