<?php
class Application_Model_Kernel_Block {

	protected $idBlock;
	protected $idContentPack;
    protected $blockName;
	/**
	 * @var Application_Model_Kernel_Content_Manager
	 */
	protected $_contentManager = null;
	/**
	 * @var Application_Model_Kernel_Content_Lang
	 */
	protected $_content = null;
	
	const STATUS_SHOW = 1;
	const STATUS_SYSTEM = 2;
	
	const TYPE_TEXT_BLOCK = 1;
	
	const ERROR_CONTENT_LANG_GIVEN = 'Wrong content lang given';
	const ERROR_CONTENT_MANAGER_GIVEN = 'Wrong content manager given';
	const ERROR_CONTENT_MANAGER_IS_NOT_DEFINED = 'Content manager is not defined';
	const ERROR_CONTENT_LANG_IS_NOT_DEFINED = 'Content lang model is not defined';
	
	
	public function __construct( $idBlock, $idContentPack, $blockName ) {
		$this->idBlock =  $idBlock;
		$this->idContentPack = $idContentPack;
        $this->blockName = $blockName;
	}
	
	public function getId() {
		return $this->idBlock;
	}
	
	/**
	 * @param Application_Model_Kernel_Content_Manager $contentManager
	 * @throws Exception ERROR_CONTENT_MANAGER_GIVEN
	 * @return Application_Model_Kernel_Catalog_Good
	 */
	public function setContentManager(Application_Model_Kernel_Content_Manager &$contentManager) {
		$this->_contentManager = $contentManager;
		return $this;
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
	
	public function setName($name) {
		$this->blockName = $name;
	}
	
	public function getName() {
		return $this->blockName;
	}
	
	public function validate() {
		if ($this->getName() === '') {
			throw new Exception('Enter block name');
		}
		if (strlen($this->getName()) <= 3) {
			throw new Exception('Block name must me more then 3 letter');
		}
	}
	
	/**
	 * Save block data
	 * @access public
	 * @return void
	 */
	public function save() {
		$data = array(
			'idContentPack' => $this->idContentPack,
			'blockName' => $this->blockName
		);
		$db = Zend_Registry::get('db');
		if (is_null($this->idBlock)) {
			$this->idContentPack = $this->getContentManager()
										->saveContentData()
										->getIdContentPack();//ставим AI idContent
			$data['idContentPack'] = $this->idContentPack;
			$db->insert('blocks', $data);
			$this->idBlock = $db->lastInsertId();
		} else {
            $this->getContentManager()->saveContentData();//Сохраняем весь конент через меджер
			$db->update('blocks', $data, 'idBlock = ' . $this->getId());
		}
	}
	
	public static function getList($content = false, $page = false, $countOnPage = false, $limit = false) {
		$return = new stdClass();
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('blocks');
		if ($content) {
			$select->join('content', 'content.idContentPack = blocks.idContentPack');	
			$select->where('content.idLanguage = ?', Kernel_Language::getCurrent()->getId());
		}
		if ($limit !== false && $page === false)
			$select->limit($limit);
		if ($page !== false) {
			$paginator = Zend_Paginator::factory($select);
			$paginator->setItemCountPerPage($countOnPage);
			$paginator->setPageRange(40);
			$paginator->setCurrentPageNumber($page);
			$return->paginator = $paginator;
		} else {
			$return->paginator = $db->fetchAll($select);
		}
		$return->data = array();
		$i = 0;
		foreach ($return->paginator as $blockData){
			$return->data[$blockData->blockName] = new self($blockData->idBlock, $blockData->idContentPack, $blockData->blockName);
			if ($content) {
				$contentLang = new Application_Model_Kernel_Content_Language($blockData->idContent, $blockData->idLanguage, $blockData->idContentPack);
                $contentLang->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($blockData->idContent));
				$return->data[$blockData->blockName]->setContent( $contentLang );
			}
			$i++;
		}
		return $return;
	}
	
	public static function getById($idBlock) {
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('blocks');
		$select->where('blocks.idBlock = ?', $idBlock);
		$select->limit(1);
		if (($block = $db->fetchRow($select)) !== false) {
			return new self($block->idBlock, $block->idContentPack, $block->blockName, $block->blockStatus, $block->blockType, $block->blockEditDate, $block->blockPosition);
		} else {
			throw new Exception('BLOCK NOT FOUND');
		}
	}
	
	public function delete() {
		$db = Zend_Registry::get('db');
		if ($this->getStatus() !== self::STATUS_SYSTEM) {
			$db->delete('blocks',"blocks.idBlock = {$this->getId()}");
			$this->getContentManager()->delete();
		}
	}

	public function increasePosition() {
		$db = Zend_Registry::get('db');
		$db->update('blocks', array('blockPosition' => new Zend_Db_Expr('blockPosition + 1')), 'blockType = ' . $this->getType());
	}
	
}