<?php
class Kernel_Language {
	
	private $_idLang;
	private $_isoName;
	private $_fullName;
	private $_customName;
	private $_localeStatus;
	
	private static $currentIsoName = null;
	
	const STATUS_ACTIVE = 1;
	const STATUS_DISABLED = 0;
	
	const DEFAULT_LANG = 'ru_RU';
	
	const ERROR_EMPTY_LANG_TABLE = "Empty Language Table";
	const ERROR_INVALID_LANG_ISO_NAME = 'Invalid route iso name';
	const ERROR_INVALID_LANG_ID = 'Invalid lang id';
	
	public function __construct($idLang, $isoName, $fullName, $customName, $localeStatus) {
		$this->_idLang = $idLang;
		$this->_isoName = $isoName;
		$this->_fullName = $fullName;
		$this->_customName = $customName;
		$this->_localeStatus = $localeStatus;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return (int)$this->_idLang;
	}

	public function getIsoName() {
		return $this->_isoName;
	}

	public function getFullName() {
		return $this->_fullName;
	}

	public function getCustomName() {
		return $this->_customName;
	}
	
	public function getStatus() {
		return intval($this->_localeStatus);
	}

	/**
	 * @name getByCustomName
	 * @param string $customName
	 * @throws Exception
	 * @return Kernel_Locale_Lang
	 */
	public static function getByCustomName($customName) {
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('langs');
		$select->where('langs.customName = ?', $customName);
		$select->limit(1);
		if ($lang = $db->fetchRow($select))
			return new self($lang->idLang, $lang->isoName, $lang->fullName, $lang->customName, $lang->localeStatus);
		else
			throw new Exception(self::ERROR_INVALID_LANG_ISO_NAME);
	}

	/**
	 * @name getById
	 * @param int $idLang
	 * @throws Exception ERROR_INVALID_LANG_ID
	 * @return Kernel_Locale_Lang
	 */
	public static function getById($idLang) {
		$idLang = (int)$idLang;
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('langs');
		$select->where('langs.idLang = ?', $idLang);
		$select->limit(1);
		if (($lang = $db->fetchRow($select)) !== false)
			return new self($lang->idLang, $lang->isoName, $lang->fullName, $lang->customName, $lang->localeStatus);
		else
			throw new Exception(self::ERROR_INVALID_LANG_ID);
	}
	
	public static function getAll() {
		$return = array();
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('langs');
                $res = $db->fetchAll($select);
		if ( $res !== false){
			foreach ( $res as $lang ) {
				$return[] = new self($lang->idLang, $lang->isoName, $lang->fullName, $lang->customName, $lang->localeStatus);
			}
			return $return;
		} else {
			throw new Exception(self::ERROR_EMPTY_LANG_TABLE);
		}
	}
	public static function getCurrent() {
        $lang = self::checkLangByUrl();
        if( $lang != false )
            return $lang;
		if(is_null(self::$currentIsoName)) {
			self::setDefaultLang();
		} 
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('langs');
		$select->where('langs.isoName = ?', self::$currentIsoName);
		$select->limit(1);
		if (($lang = $db->fetchRow($select)) !== false)
			return new self($lang->idLang, $lang->isoName, $lang->fullName, $lang->customName, $lang->localeStatus);
		else
			throw new Exception(self::ERROR_INVALID_LANG_ISO_NAME);
	}
	public static function idToIso($idLang) {
		return self::getById($idLang)->getIsoName();
	}
	public static function setDefaultLang() {
		self::$currentIsoName = self::DEFAULT_LANG;
	}
	
	public static function setCurrentName($isoName) {
		//@todo check what we set
		self::$currentIsoName = $isoName;
	}
    public static function checkLangByUrl() {
        if( mb_strlen($_SERVER['REQUEST_URI'],'utf8') > 3 && mb_substr($_SERVER['REQUEST_URI'], 1,1) !== "?" ){
            $langs = self::getAll();
            $lang = substr($_SERVER['REQUEST_URI'],1,2);
            foreach( $langs as $key=>$value ){
                if( $value->getCustomName() == $lang ){
                    self::$currentIsoName = $value->getIsoName();
                    return $value;
                }
            }
        }
        return false;
    }
    
}