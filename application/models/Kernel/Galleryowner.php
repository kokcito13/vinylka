<?php
class Application_Model_Kernel_Galleryowner {
	
	private $idGalleryOwner;
	private $idContentPack;
	private $galleryOwnerType;
    private $galleryOwnerStatus;
    private $galleryOwnerView;
    private $galleryOwnerWeek;
    private $galleryOwnerDate;
    
    private $idPhoto;
    
    private $photo = null;
    
    const LIMIT_ON_NEWS_PAGE = 3;   
    
	private  $_contentManager = null;
	private $_content = null;
	
	const STATUS_SHOW = 1;
	const STATUS_HIDE = 0;
    
    const TYPE_PICTURE = 1;
	const TYPE_PHOTO = 2;
    
    const PISTURE_ON_PAGE = 12;
    
		
	public function __construct($idGalleryOwner, $idContentPack, $idPhoto, $galleryOwnerType, $galleryOwnerStatus = self::STATUS_SHOW, $galleryOwnerView = 0, $galleryOwnerWeek = 0, $galleryOwnerDate = 0) {
		$this->idGalleryOwner = $idGalleryOwner;
		$this->idContentPack = $idContentPack;
        $this->idPhoto = $idPhoto;
		$this->galleryOwnerType = $galleryOwnerType;
        $this->galleryOwnerStatus = $galleryOwnerStatus;
        $this->galleryOwnerView = $galleryOwnerView;
        $this->galleryOwnerWeek = $galleryOwnerWeek;
        $this->galleryOwnerDate = $galleryOwnerDate;
	}
	
	public function getId() {
		return $this->idGalleryOwner;
	}
	
	public function getIdPhoto(){
        return $this->idPhoto;
    }
    
    public function getGalleryOwnerDate() {
		return $this->galleryOwnerDate;
	}
    
    public function getPhoto() {
        if (is_null($this->photo))
            $this->photo = Application_Model_Kernel_Photo::getById($this->idPhoto);
        return $this->photo;
    }
    
    public function setPhoto(Application_Model_Kernel_Photo &$photo) {
        $this->photo = $photo;
        return $this;
    }
    public function setIdPhoto($idPhoto){
        $this->idPhoto = $idPhoto;
    }
	
	public function getStatus() {
		return $this->galleryOwnerStatus;
	}
    
    public function getGalleryOwnerWeek() {
		return $this->galleryOwnerWeek;
	}
    
    public function setGalleryOwnerWeek($item){
        $this->galleryOwnerWeek = $item;
    }
	
    public function getGalleryOwnerView(){
        return $this->galleryOwnerView;
    }

    public function setGalleryOwnerView($item){
        $this->galleryOwnerView = $item;
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
	
	public static function getById($idGalleryOwner) {
		$idGalleryOwner = (int)$idGalleryOwner;
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('gallery_owner');
		$select->where('gallery_owner.idGalleryOwner = ?', $idGalleryOwner);
		if (false !== ($data = $db->fetchRow($select))) {
			return self::getSelf($data);
		} else
			throw new Exception(self::TYPE_ERROR_ID_NOT_FOUND);
	}
    
	public static function getSelf($data){
		return new self($data->idGalleryOwner, $data->idContentPack, $data->idPhoto, 
                $data->galleryOwnerType, $data->galleryOwnerStatus, $data->galleryOwnerView, 
                $data->galleryOwnerWeek, $data->galleryOwnerDate);
	}
	public static function getList($status = false, $type = false, $order = false, $page = false, $onPage = false) {
		$db = Zend_Registry::get('db');
		
		$select = $db->select()->from('gallery_owner');
        if( $status != false )
            $select->where('gallery_owner.galleryOwnerStatus = '.self::STATUS_SHOW);
        if( $type != false )
            $select->where('gallery_owner.galleryOwnerType = '.$type);
        if( $order == false)
            $select->order(' gallery_owner.idGalleryOwner DESC ');
        else 
            $select->order($order); 
        
        if( $page !== false ){
            $return = new stdClass();
            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($onPage);
            $paginator->setPageRange(40);
            $paginator->setCurrentPageNumber($page);
            $return->paginator = $paginator;
            $return->data = array();
            $i = 0;
            foreach ($return->paginator as $data) {
                $return->data[$i] = self::getSelf($data);
                $i++;
            }
            return $return;
        }
        $return = array();
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
            'idPhoto' => $this->idPhoto,
			'galleryOwnerType' => $this->galleryOwnerType,
			'galleryOwnerStatus' => $this->galleryOwnerStatus,
            'galleryOwnerView' => $this->galleryOwnerView,
            'galleryOwnerWeek' => $this->galleryOwnerWeek,
            'galleryOwnerDate' => $this->galleryOwnerDate
		);
		$db = Zend_Registry::get('db');
		if (is_null($this->idGalleryOwner)) {
			if (is_null($this->_contentManager))
				throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
				$this->_contentManager->saveContentData();//Сохраняем весь конент через меджер
			$this->idContentPack = $this->_contentManager->getIdContentPack();//ставим AI idContent
			$data['idContentPack'] = $this->idContentPack;
			$db->insert('gallery_owner', $data);
			$this->idGalleryOwner = $db->lastInsertId();
		} else {
			$this->getContentManager()->saveContentData();
			$db->update('gallery_owner', $data, 'idGalleryOwner = ' . intval($this->idGalleryOwner));
		}
	}

	public function show() {
		$db = Zend_Registry::get('db');
		$db->update('gallery_owner',array(
			'gallery_owner.galleryOwnerStatus' => self::STATUS_SHOW
		),"gallery_owner.idGalleryOwner = {$this->idGalleryOwner}");
	}
	
	public function hide() {
		$db = Zend_Registry::get('db');
		$db->update('gallery_owner',array(
			'gallery_owner.galleryOwnerStatus' => self::STATUS_HIDE
		),"gallery_owner.idGalleryOwner = {$this->idGalleryOwner}");
	}
	
	public function delete() {
		$db = Zend_Registry::get('db');
		$db->delete('gallery_owner',"gallery_owner.idGalleryOwner = {$this->idGalleryOwner}");
		$this->getContentManager()->delete();
	}
	public static function changePosition($id, $position) {
        $db = Zend_Registry::get('db');
        $db->update('gallery_owner', array("galleryOwnerStatus" => $position), 'gallery_owner.idGalleryOwner = ' . (int) $id);

        for ($i = 0; $i < 2000; $i++) {
            
        }
        return true;
    }
}