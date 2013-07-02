<?php
class Application_Model_Kernel_Gallery {
	
	const HEADER = 58;
	
	private $_idGallery = null;
	private $_createDate;
	private $_editDate;
	private $_position;
	private $_count = null;

	public function __construct($idGallery, $galleryCreateDate, $galleryEditDate, $galleryPosition) {
		$this->_idGallery = $idGallery;
		$this->_createDate = $galleryCreateDate;
		$this->_editDate = $galleryEditDate;
		$this->_position = $galleryPosition;
	}

	public function getId() {
		return $this->_idGallery;
	}
	
	public function getGalleryCreateDate($type) {
		return Application_Model_Kernel_Date_Manager::formatDate($this->_createDate, $type);
	}
	
	public function getGalleryEditDate($type) {
		return Application_Model_Kernel_Date_Manager::formatDate($this->_editDate, $type);
	}
	
	public function getFirstPhoto() {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from('galleryPhotos');
		$select->join('photos', 'galleryPhotos.idPhoto = photos.idPhoto');
		$select->where('idGallery = ? ', intval($this->getId(0)));
		$select->order('photos.photoPosition ASC');
		$select->limit(1);
		$photo = $db->fetchRow($select);
		return new Application_Model_Kernel_Photo($photo->idPhoto, $photo->photoPath, $photo->photoAlt, $photo->photoPosition);
	}
	
	public function getPhotoCount() {
		if (is_null($this->_count)) {
			$db = Zend_Registry::get('db');
			$select = $db->select();
			$select->from('galleryPhotos',array(
				'count'=>'COUNT(photos.idPhoto)'
			));
			$select->join('photos', 'galleryPhotos.idPhoto = photos.idPhoto');
			$select->where('idGallery = ? ', intval($this->getId()));
			$this->_count = (int)$db->fetchRow($select)->count;
		}
		return $this->_count;
	}

	/**
	 * @param int $idGallery
	 * @throws Exception
	 * @return Application_Model_Kernel_Gallery
	 */
	public static function getById($idGallery) {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from('gallery');
		$select->where('idGallery = ?', intval($idGallery));
		$select->limit(1);
		if (false !== ($result = $db->fetchRow($select)))
			return new self($result->idGallery, $result->galleryCreateDate, $result->galleryEditDate, $result->galleryPosition);
		else
			throw new Exception('Gallery with id ' . intval($idGallery) . ' not exists');
	}
	
	/**
	 * @name save
	 * @return Application_Model_Kernel_Gallery
	 */
	public function save() {
		$db = Zend_Registry::get('db');
		$data = array(
			'galleryCreateDate' => $this->_createDate,
			'galleryEditDate' => time(),
			'galleryPosition' => $this->_position
		);
		if (is_null($this->_idGallery)) {
			$db->insert('gallery', $data);
			$data['galleryPosition'] = 0;
			$this->_idGallery = $db->lastInsertId();
		} else {
			$db->insert('gallery', $data, 'idGallery = ' . intval($this->_idGallery));
		}
		return $this;
	}
	
	/**
	 * @name getPhotos
	 * @access public 
	 * @return array Application_Model_Kernel_Photo
	 */
	public function getPhotos() {
		return self::getGalleryPhotos($this->getId());
	}
	
	/**
	 * Get gallery photos
	 * 
	 * @access public
	 * @param int $idGallery
	 * @static
	 * @return array
	 */
	public static function getGalleryPhotos($idGallery) {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from('galleryPhotos');
		$select->join('photos', 'galleryPhotos.idPhoto = photos.idPhoto');
		$select->where('idGallery = ? ', intval($idGallery));
		$select->order('photos.photoPosition DESC');
		$photos = array();
		if (false !== ($result = $db->fetchAll($select))) {
			foreach ($result as $photo) {
				$photos[] = new Application_Model_Kernel_Photo($photo->idPhoto, $photo->photoPath, $photo->photoAlt, $photo->photoPosition);
			}
		}
		return $photos;
	}

	/**
	 * Add given photo to gallery
	 * 
	 * @access public
	 * @param Application_Model_Kernel_Photo $photo
	 * @throws Exception
	 * @return void
	 */
	public function addPhotoToGallery(Application_Model_Kernel_Photo &$photo) {
		$db = Zend_Registry::get('db');
		if (is_null($photo->getId())) {
			throw new Exception('UNSAVED PHOTO GIVEN TO GALLERY');
		}
		if (is_null($this->_idGallery)) {
			throw new Exception('SAVE GALLERY BEFORE ADD PHOTO');
		}
		$db->insert('galleryPhotos', array(
			'idGallery' => $this->_idGallery,
			'idPhoto' => $photo->getId()
		));
	}
	
	public function delete() {
		$db = Zend_Registry::get('db');
		$db->delete('gallery',"gallery.idGallery = {$this->getId()}");
		foreach (self::getGalleryPhotos($this->getId()) as $photo) {
			$photo->delete();
		}
	}
	
	public function sortPhotos(array $sortData) {
		$db = Zend_Registry::get('db');
		foreach ($sortData as $position => $photoId) {
			$photo = Application_Model_Kernel_Photo::getById(intval($photoId));
			if ($position != $photo->getPosition()) {
				$photo->setPosition($position)->save();
			}
		}
	}
	
}