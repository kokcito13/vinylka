<?php
/**
 * Application_Model_Kernel_Content_Language
 * 
 * Manage content of one lang
 * 
 * @author <vlad.melanitski@gmail.com>
 * @package Content
 * @version 1.0
 */
class Application_Model_Kernel_Content_Language {
	
	private $_idContent;
	private $_idLang;
	private $_idContentPack;
	private $fields = array();
	
	const ERROR_INVALID_CONTENT_NAME = 'Entered incorrect name';
	const ERROR_CONTENT_NOT_EXISTS = 'Such content not exsit';
	const ERROR_NO_FILDS_CONTENT = 'Not filds in this content';
	
	public function __construct($idContent, $idLang, $idContentPack) {
		$this->_idContent = $idContent;
		$this->_idLang = $idLang;
		$this->_idContentPack = $idContentPack;
		$this->fields = $this->getFields();
	}
	public function getIsoName() {
		return Kernel_Language::idToIso($this->_idLang);
	}
	public function getFields() {
		return $this->fields;
	}
	public function getContentName() {
		return $this->_contentName;
	}
	public function getId() {
		return $this->_idContent;
	}
	public function getIdLang() {
		return $this->_idLang;
	}
	public function getIdContentPack() {
		return $this->_idContentPack;
	}
	public function setIdContentPack($idContentPack) {
		$this->_idContentPack = intval($idContentPack);
	}
	public function setFields($key, $value, $idField = 0){
		$this->fields[] = new Application_Model_Kernel_Content_Fields($idField, $this->_idContent, $key, $value);
	}
	public function setFieldsArray($fields){
		$this->fields = $fields;
	}
	public function save() {
		$data = array(
			'idContent' => $this->_idContent,
			'idLanguage' => $this->_idLang,
			'idContentPack' => $this->_idContentPack);
		//'content' => $this->clearStyle($this->_content),
		$db = Zend_Registry::get('db');
		if (is_null($this->_idContent)) {
			$db->insert('content', $data);
			$this->_idContent = $db->lastInsertId();
		} else {
			$db->update('content', $data, 'idContent = ' . intval($this->_idContent));
		}
		if(!is_null($this->getFields())) {
			$this->saveField();
		}
		//$this->clearCache();
	}
	private function saveField() {
		if(!is_null($this->fields)){
			$db = Zend_Registry::get('db');
			foreach ($this->fields as $field) {
				if($field->getIdField() == 0) {
					$dataInsert = array(
						'idField'=>NULL,
						'idContent'=>$this->_idContent,
						'fieldName'=>$field->getFieldName(),
						'fieldText'=>$field->getFieldText()					
					);
					$db->insert('fields', $dataInsert);
				} else {
					$dataUpdate = array(
						'fieldName'=>$field->getFieldName(),
						'fieldText'=>$field->getFieldText()					
					);
					$db->update('fields', $dataUpdate, 'idField = ' . (int)$field->getIdField());
				}
			}
		} else {
			throw new Exception(self::ERROR_NO_FILDS_CONTENT);
		}
	}
	private function clearCache() {
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache('langs');
		$cache->remove($this->getId());
		$cache->remove($this->getIdContentPack());
		foreach (Kernel_Language::getAll() as $Language) {
			$cache->remove(('CONTENT_' . $this->getIdContentPack() . '_LANG_' . $Language->getId()));
		}
	}
	
	public function delete() {
		$db = Zend_Registry::get('db');
		$db->delete('content', "idContent = " . intval($this->_idContent));
//		$this->clearCache();
	}
	
	/**
	 * @param int $contentId
	 * @throws Exception
	 * @return Application_Model_Kernel_Content_Language
	 */
	public static function getById($contentId) {
//		$cachemanager = Zend_Registry::get('cachemanager');
//		$cache = $cachemanager->getCache('langs');
//		if (($lang = $cache->load($contentId)) !== false) {
//			return $lang;
//		} else {
			$db = Zend_Registry::get('db');
			$select = $db->select->from('content');
			$select->where('content.idContent = ?', intval($contentId));
			if (($result = $db->fetchRow($select)) !== false) {
				$lang = new self($result->idContent, $result->idLang, $result->idContentPack);
				$lang->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($result->idContent));
//				$cache->save($lang);
				return $lang;
			} else {
				throw new Exception(self::ERROR_ID_CONTENT_NOT_EXISTS);
			}
//		}
	}
	
	public static function get($idContentPack, $idLang) {
		//$cachemanager = Zend_Registry::get('cachemanager');
		//$cache = $cachemanager->getCache('langs');
		//if (($lang = $cache->load('CONTENT_' . $idContentPack . '_LANG_' . $idLang)) !== false) {
		//	return $lang;
		//} else {
			$db = Zend_Registry::get('db');
			$select = $db->select()->from('content');
			$select->where('content.idContentPack = ?', intval($idContentPack));
			$select->where('content.idLanguage = ?', intval($idLang));
			if (($result = $db->fetchRow($select)) !== false) {
				$lang = new  self($result->idContent, $result->idLanguage, $result->idContentPack);
				$lang->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($result->idContent));
				//$cache->save($lang);
				return $lang;		
			} else {
				throw new Exception(self::ERROR_CONTENT_NOT_EXISTS);
			}
		//}
	}
	
	protected function clearStyle($Content) {
		if (preg_match('/(style\=[a-z0-9\;\,\"\'\ \.\-\:\#\%]+)/i',$Content,$result)) {
			$Content = str_replace($result[1],"",$Content);
			$Content = $this->clearStyle($Content);
		}
		return str_replace(array(
			'<pre>',
			'</pre>'
		), array(
			'<p>',
			'</p>'
		), $Content);
	}
        
        public function validate(Application_Model_Kernel_Exception &$e){
            
        }
}
