<?php
/**
 * Save errors to DB row
 * @author vlad.melanitski@gmail.com
 */

class Application_Model_Kernel_ErrorLog {
	
	const ID_SAVE_ERROR = 1;
	const ID_FIND_CITY = 2;	
	const ID_PARSE_ERROR = 3;

	private static function isIdLog($id) {
		return in_array($id, array(
			self::ID_FIND_CITY,
			self::ID_SAVE_ERROR,
			self::ID_PARSE_ERROR
		));
	}
	
	public static function addLogRow($idLog, $errorText) {
		$db = Zend_Registry::get('db');
		if (self::isIdLog($idLog)) {
			$db->insert('_errorLogRow',array(
				'idLog' => $idLog,
				'errorTime' => date('Y-m-d H:i:s'),
				'errorText' => $errorText,
				'errorUrl' => $_SERVER['REQUEST_URI']
			));
		}
	}
	
}