<?php
class Zend_View_Helper_CutImageUrl {
	
	const TYPE_LOGO = 1;
	const TYPE_LOGO_WIDTH = 122;
	const TYPE_LOGO_HEIGHT = 86;
	
	const TYPE_PHOTO = 2;
	const TYPE_PHOTO_WIDTH = 150;
	const TYPE_PHOTO_HEIGHT = 150;
	
	public function CutImageUrl($path,$type,$unique = true) {
		$url = '/image.php?';
		switch ($type) {
			case self::TYPE_LOGO:
				$url.='width=' . self::TYPE_LOGO_WIDTH . '&cropratio=3.05:2.15&height=' . self::TYPE_LOGO_HEIGHT;
			break;
			case self::TYPE_PHOTO:
				$url.='width=' . self::TYPE_PHOTO_WIDTH . '&cropratio=1:1&height=' . self::TYPE_PHOTO_HEIGHT;
			break;
			default:
				throw new Exception('ERROR TYPE CutImageUrl');
			break;
		}
		$url.='&image=' . $path;
		if ($unique) 
			$url.='&time=' . time();
		return $url;
	}
	
}