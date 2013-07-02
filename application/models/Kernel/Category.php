<?php
class Application_Model_Kernel_Category {
	
	protected $_idCategory;
	protected $_idParentCategory;
	protected $_idContentPack;
	protected $_categoryEditTime;
	protected $_categoryStatus;
	protected $_position;
	protected $_url;
	protected $_childrenCount;
    
    protected $categoryList = array();


    /**
	 * @var Application_Model_Kernel_Content_Manager
	 */
	private  $_contentManager = null;
	/**
	 * @var Application_Model_Kernel_Content_Lang
	 */
	private $_content = null;
	/**
	 * @var Application_Model_Kernel_Category
	 */
	private $_parentCategory;
	/**
	 * @var array
	 */
	private $_childrenNodes = NULL;
	
	const STATUS_SHOW = 1;
	const STATUS_HIDE = 0;
	
	const ERROR_CONTENT_LANG_GIVEN = 'Wrong content lang given';
	const ERROR_CONTENT_MANAGER_GIVEN = 'Wrong content manager given';
	const ERROR_CONTENT_MANAGER_IS_NOT_DEFINED = 'Content manager is not defined';
	const ERROR_CONTENT_LANG_IS_NOT_DEFINED = 'Content lang model is not defined';
	const TYPE_ERROR_ID_NOT_FOUND = 'Id not found';

	
	public function __construct($idCategory, $idParentCategory, $idContentPack, $categoryEditTime, $categoryStatus, $position, $childrenCount = null, $url = false) {
		$this->_idCategory = $idCategory;
		$this->_idParentCategory = $idParentCategory;
		$this->_idContentPack = $idContentPack;
		$this->_categoryEditTime = $categoryEditTime;
		$this->_categoryStatus = $categoryStatus;
		$this->_position = $position;
        if (!$url) {
            $url = substr(md5(time()),0,6);
        }
        $this->_url = $url;
        $this->_childrenCount = $childrenCount;
    }
	
		
	/**
	 * @name getById
	 * @param int $idCategory
	 * @throws Exception
	 * @return Application_Model_Kernel_Category
	 */
	public static function getById($idCategory) {
		$idCategory = (int)$idCategory;
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('categories');
		$select->where('categories.idCategory = ?', $idCategory);
		if (false !== ($data = $db->fetchRow($select))) {
			return self::getSelf($data);
		} else
			throw new Exception(self::TYPE_ERROR_ID_NOT_FOUND);
	}
	
	public function getId() {
		return $this->_idCategory;
	}
	
	public function getCountChildrenNode() {
		if (is_null($this->_childrenCount))
			$this->_childrenCount = sizeof($this->getChildrenNodes());
		return $this->_childrenCount;
	}
	
	public function setParentId($idParent) {
		$idParent = intval($idParent);
		if ($idParent === 0)
			$this->_idParentCategory = NULL;
		else {
			$this->_idParentCategory = $idParent;
			$this->getParentNode();
		}
	}
	
	public function getParentId() {
		return $this->_idParentCategory;
	}
	
	public function getStatus() {
		return $this->_categoryStatus;
	}

    public function isStatusShow()
    {
        if (!$this->getStatus()) {
            return false;
        }

        return true;
    }
	
	public function getPosition() {
		return $this->_position;
	}
	
	public function setPosition($position) {
		$this->_position = intval($position);
	}

    public function getUrl()
    {

        return $this->_url;
    }

    public function setUrl($item)
    {
        $this->_url = $item;

        return $this;
    }

	
	public function setChildrenNodes(array $nodes) {
		$this->_childrenNodes = array();
		foreach ($nodes as $category) {
			if (get_class($category) == 'Application_Model_Kernel_Category') {
				$this->_childrenNodes[] = $category;
			} else {
				throw new Exception('NOT CATEGORY ARRAY GIVEN TO METHOD setChildrenNodes');
			}
		}
	}
	
	public function getChildrenNodes() {
		if (is_null($this->_childrenNodes)) {
			$db = Zend_Registry::get('db');
			$select = $db->select()->from('categories');
			$select->where('categories.idParentCategory = ?', intval($this->getId()));
			$select->order('position DESC');
			$categories = array();
			if (false !== ($result = $db->fetchAll($select))) {
				foreach ($result as $category) {
					$categories[$category->idCategory] = self::getSelf($category);
				}
			}
			$this->_childrenNodes = $categories;
		}
		return $this->_childrenNodes;
	}
	
	/**
	 * 
	 * @return Application_Model_Kernel_Category
	 */
	public function getParentNode() {
		if (is_null($this->_parentCategory))
			$this->_parentCategory = self::getById($this->_idParentCategory);
		return $this->_parentCategory;
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
			$this->_contentManager = Application_Model_Kernel_Content_Manager::getById($this->_idContentPack);
		}
		return $this->_contentManager;
	}
	
	/**
	 * @return Application_Model_Kernel_Content_Lang
	 */
	public function getContent() {
		if (is_null($this->_content)) {
			$this->_content = Application_Model_Kernel_Content_Language::get($this->_idContentPack, 1);
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
	
	public function updatePosition() {
		$db = Zend_Registry::get('db');
		$idParent = intval($this->_idCategory);
		if ($idParent === 0)
			$where = 'categories.idParentCategory IS NULL';
		else
			$where = 'categories.idParentCategory = '. intval($idParent);
		$db->update('categories', array('position' => new Zend_Db_Expr('position + 1')), $where);
	}
	public static function getSelf($data){
		return new self($data->idCategory, $data->idParentCategory, $data->idContentPack, $data->categoryEditTime, $data->categoryStatus, $data->position, null, $data->url);
	}
	public static function getList() {
		$db = Zend_Registry::get('db');
		$return = array();
		$select = $db->select()->from('categories');
		$select->where('categories.idParentCategory IS NOT NULL');
		if (false !== ($result = $db->fetchAll($select))) {
			foreach ($result as $category) {
				$return[] = self::getSelf($category);
			}
		}
		return $return;
	}
	public static function getListParent() {
		$db = Zend_Registry::get('db');
		$return = array();
		$select = $db->select()->from('categories');
		$select->where('categories.idParentCategory IS NULL');
		if (false !== ($result = $db->fetchAll($select))) {
			foreach ($result as $category) {
				$return[] = self::getSelf($category);
			}
		}
		return $return;
	}
	public function save() {
		$data = array(
			'idParentCategory' => $this->_idParentCategory,
			'idContentPack' => $this->_idContentPack,
			'categoryEditTime' => $this->_categoryEditTime,
			'categoryStatus' => $this->_categoryStatus,
			'position' => $this->_position,
            'url' => $this->_url
		);
		$db = Zend_Registry::get('db');
		if (is_null($this->_idCategory)) {
			if (is_null($this->_contentManager))
				throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
				$this->_contentManager->saveContentData();//Сохраняем весь конент через меджер
			$this->_idContentPack = $this->_contentManager->getIdContentPack();//ставим AI idContent
			$data['idContentPack'] = $this->_idContentPack;
			$this->updatePosition();
			$db->insert('categories', $data);
			$this->_idCategory = $db->lastInsertId();
		} else {
			$this->getContentManager()->saveContentData();
			$db->update('categories', $data, 'idCategory = ' . intval($this->_idCategory));
		}
	}

	public static function getStructCategories($content) {
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('categories');
		if ($content) {
			$select->join('content', 'content.idContentPack = categories.idContentPack');	
			$select->where('content.idLanguage = ?', Kernel_Language::getCurrent()->getId());
		}
		$select->order('position DESC');
		$resultParent = array();
		$i = 0;
		if (false !== ($result = $db->fetchAll($select))) {
			foreach ($result as $category) {
				$category->idParentCategory = intval($category->idParentCategory);
				if (!isset($resultParent[$category->idParentCategory])) {
					$resultParent[$category->idParentCategory] = array();
				}
				$resultParent[$category->idParentCategory][$i] = self::getSelf($category);
				$resultParent[$category->idParentCategory][$i]->setChildrenNodes(array());
				if ($content) {
					$contentLang = new Application_Model_Kernel_Content_Language($category->idContent, $category->idLanguage, $category->idContentPack);
					$resultParent[$category->idParentCategory][$i]->setContent($contentLang);
				}
				++$i;
			}
		}
		ksort($resultParent, SORT_NUMERIC);
		$resultParent = array_reverse($resultParent, true);
		foreach ($resultParent as $idParentCategory => $appendCategories) {
			foreach ($resultParent as $idParent => &$categories) {
				foreach ($categories as $i => &$category) {
					if ($idParentCategory == $category->getId()) {
						$category->setChildrenNodes($resultParent[$idParentCategory]);
						unset($resultParent[$idParentCategory]);
					}
 				}
			}
		}
		if (isset($resultParent[0]))
			return $resultParent[0];
		else
			return array();
	}
	
	public static function getCategoriesByParentId($idParent) {
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('categories');
		if (is_null($idParent))
			$select->where('categories.idParentCategory IS NULL');
		else
			$select->where('categories.idParentCategory = ?', (int)$idParent);
		$select->order('position DESC');
		$categories = array();
		if (false !== ($result = $db->fetchAll($select))) {
			foreach ($result as $category) {
				$categories[] = self::getSelf($category);
			}
		}
		return $categories;
	}

	public function show() {
		$db = Zend_Registry::get('db');
		$db->update('categories',array(
			'categories.categoryStatus' => self::STATUS_SHOW
		),"categories.idCategory = {$this->_idCategory}");
	}
	
	public function hide() {
		$db = Zend_Registry::get('db');
		$db->update('categories',array(
			'categories.categoryStatus' => self::STATUS_HIDE
		),"categories.idCategory = {$this->_idCategory}");
	}
	
	private function deleteChildrenNodes() {
		$childrenNodes = $this->getChildrenNodes();
		if (sizeof($childrenNodes)) {
			foreach ($childrenNodes as $category) {
				$category->delete();
			}
		}
	}

	public function delete() {
		$db = Zend_Registry::get('db');
		$this->deleteChildrenNodes();
		$db->delete('categories',"categories.idCategory = {$this->_idCategory}");
		$this->getContentManager()->delete();
	}
    
    public function deleteProducts() {
        $db = Zend_Registry::get('db');
        $db->delete('categorie_product', "categorie_product.idCategorie = " . $this->_idCategory);
    }
	
	public function move($toId, $moveType) {
		if (in_array($moveType, array(self::TYPE_MOVE_UP, self::TYPE_MOVE_DOWN))) {
			$to = self::getById($toId);
			if ($this->getParentId() == $to->getParentId()) {
				$tmpPosition = $this->getPosition();
				switch ($moveType) {
					case self::TYPE_MOVE_UP:
						if ($this->getPosition() >= $to->getPosition())
							throw new Exception('WRONG ELEMENTS GIVEN FOR MOVING UP');
					break;
					case self::TYPE_MOVE_DOWN:
						if ($this->getPosition() <= $to->getPosition())
							throw new Exception('WRONG ELEMENTS GIVEN FOR MOVING DOWN');
					break;
				}
				$this->setPosition($to->getPosition());
				$to->setPosition($tmpPosition);
				unset($tmpPosition);
				$to->save();
				$this->save();
			} else {
				throw new Exception('MOVING NODES HAVE DIFERENT PARENTS');
			}
		} else {
			throw new Exception('NOT SIMPLE MOVE TYPE');
		}
	}
    public function getListIdProductByCategory(){
        if (count($this->categoryList) == 0) {
            $db = Zend_Registry::get('db');
            $return = array();
            $select = $db->select()->from('type_product');
            $select->where('type_product.idCategory = ' . (int) $this->_idCategory);
            $i = 0;
            if (false !== ($result = $db->fetchAll($select))) {
                foreach ($result as $category) {
                    $object[$i] = new stdClass();
                    $object[$i]->id = $category->id;
                    $object[$i]->idCategorie = $category->idCategorie;
                    $object[$i]->idProduct = $category->idProduct;
                    $return[] = $object[$i];
                    $i++;
                }
            }
            $this->categoryList = $return;
        } else {
            $return = $this->categoryList;
        }
        return $return;
    }

    public function getProductsIdsByTypes($ids)
    {
        $db = Zend_Registry::get('db');
        $return = array();
        $select = $db->select()->from('type_product');
        $select->where('type_product.idCategory IN ('.join(',',$ids).')');
        $i = 0;
        if (false !== ($result = $db->fetchAll($select))) {
            foreach ($result as $category) {
                $return[] = $category->idProduct;
                $i++;
            }
        }

        return $return;
    }
}