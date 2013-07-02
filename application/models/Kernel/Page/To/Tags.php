<?php
class Application_Model_Kernel_Page_To_Tags {

	private $_idPage;
	private $_tagList;

	public function __construct($idPage, $tags) {
		$this->_idPage = $idPage;
		$this->_tagList = $tags;//@todo check what we set, May be it's dick	
	}

	public function save() {
		$db = Zend_Registry::get('db');
		$db->delete('tagsToPage', 'idPage = ' . $this->_idPage );
		foreach ($this->_tagList as $tag) {
			$db->insert('tagsToPage', array(
				'idTag' => $tag->getIdTag(),
				'idPage' => $this->_idPage
			));
		}
	}

	public function getTagList() {
		return $this->_tagList;
	}
	
	public function setIdPage($idPage) {
		$this->_idPage = $idPage;
	}
	
	public function getContent() {
		$tagContent = array();
		foreach ($this->_tagList as $tag) {
			$tagContent[] = $tag->getContent();
		}
		return $tagContent;
	}
	
	/**
	 * @access public
	 * @static
	 * @param int $idPage
	 * @return Application_Model_Kernel_Page_To_Tags
	 */
	public static function getByIdPage($idPage, $content) {
		$idPage = (int)$idPage;
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('tagsToPage');
		$select->join('tags', 'tagsToPage.idTag = tags.idTag');
		if ($content) {
			$select->join('content', 'content.idContentPack = tags.idContentPack');	
			$select->where('content.idLang = ?', Kernel_Language::getCurrent()->getId());
		}
		$select->where('tagsToPage.idPage = ?',$idPage);
		$tags = array();
		$i = 0;
		if (($result = $db->fetchAll($select)) !== false) {
			foreach ($result as $tag) {
				$tags[$i] = new Application_Model_Kernel_Tag($tag->idTag, $tag->idContentPack, $tag->tagStatus, $tag->tagType);
				if ($content) {
					$tags[$i]->setContent(new Application_Model_Kernel_Content_Lang($tag->idContent, $tag->idLang, $tag->idContentPack, $tag->contentName, $tag->title, $tag->description, $tag->keywords, $tag->content, $tag->preview, $tag->baggageFiled, $tag->baggageFiledTwo));
				}
				$i++;
			}
		}
		return new self($idPage, $tags);
	}

	/**
	 * @name getTagsId
	 * @return array
	 */
	public function getTagsId() {
		$tagsId = array();
		if (!empty($this->_tagList)) {
			foreach ($this->_tagList as $tag) {
				$tagsId[] = $tag->getIdTag();
			}
		}
		return $tagsId;
	}

	public static function getAllTags($type, $orderField = 'tags.idTag', $orderType = 'DESC') {
		if (Application_Model_Kernel_Tag::isTagType($type)) {
			$db = Zend_Registry::get('db');
			$select = $db->select()->from('tags',array(
					'*',
					'count' => join(array(
						'(SELECT COUNT(`idTagToPage`) ',
						'FROM `tagsToPage`',
						'JOIN `pages` ON `tagsToPage`.`idPage` = `pages`.`idPage`',
						'WHERE `tagsToPage`.`idTag` = `tags`.`idTag`', 
							'AND `pages`.`pageStatus` = ' . Application_Model_Kernel_Page::STATUS_SHOW . ')'
					))
			));
			$select->join('content', 'tags.idContentPack = content.idContentPack');
			$select->where('content.idLang = ?', Kernel_Language::getCurrent()->getId());
			$select->where('tags.tagType = ?',$type);
			$select->order($orderField . ' ' . $orderType);
			$tags = array();
			if (($result = $db->fetchAll($select)) !== false) {
				$i = 0;
				foreach ($result as $tag) {
					$tags[$i] = new Application_Model_Kernel_Tag($tag->idTag, $tag->idContentPack, $tag->tagStatus, $tag->tagType);
					$tags[$i]->setContent(new Application_Model_Kernel_Content_Lang($tag->idContent, $tag->idLang, $tag->idContentPack, $tag->contentName, $tag->title, $tag->description, $tag->keywords, $tag->content, $tag->preview, $tag->baggageFiled, $tag->baggageFiledTwo));
					$tags[$i]->setCount($tag->count);
					$i++;
				}
			}
			return $tags;
		} else {
			throw new Exception('Wrong tag type given');
		}
	}
	
}