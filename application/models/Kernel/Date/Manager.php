<?php
class Application_Model_Kernel_Date_Manager {
	
	const ERROR_DATE_TYPE = 'WRONG DATE TYPE GIVEN';
	
	const TYPE_DATE_TIMESTAMP = 1;
	const TYPE_DATE_ISO_FULL = 2;
	const TYPE_DATE_ISO = 3;
	const TYPE_USER_VIEW = 4;
	const TYPE_USER_VIEW_SHORT = 5;
	
	static function formatDate($data, $type) {
		switch ($type) {
			case self::TYPE_DATE_TIMESTAMP:
				return $data;
			break;
			case self::TYPE_DATE_ISO_FULL:
				return date('Y-m-d H:i:s', $data);
			break;
			case self::TYPE_DATE_ISO:
				return date('Y-m-d', $data);
			break;
			case self::TYPE_USER_VIEW: 
				return date('d', $data) . ' ' . self::getMounthWord(date('m', $data)) . ' ' . date('Y', $data); 
			break;
			case self::TYPE_USER_VIEW_SHORT:
				return  date('d', $data) . ' ' . self::getMounthWord(date('m', $data)); 
			break;
			default:
				throw new Exception(self::ERROR_DATE_TYPE);
			break;
		}
	}
	
	static private function getMounthWord($mount) {
		switch (intval($mount)) {
			case 1 :
				return 'января';
			break;
			case 2 :
				return 'февраля';
			break;
			case 3 :
				return 'марта';
			break;
			case 4 :
				return 'апреля';
			break;
			case 5 :
				return 'мая';
			break;
			case 6 :
				return 'июня';
			break;
			case 7 :
				return 'июля';
			break;
			case 8 :
				return 'августа';
			break;
			case 9 :
				return 'сентября';
			break;
			case 10 :
				return 'октября';
			break;
			case 11 :
				return 'ноября';
			break;
			case 12 :
				return 'декабря';
			break;
		}
	}
	
}