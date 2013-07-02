<?php
class Application_Model_Kernel_TextRedactor {
	
	public static function cutText($text, $count = 100) {
		$return = $text;
		$check = substr($text, 0, $count);
		if(trim(substr($check, $count-1, $count)) == '') return $check;
		else return self::cutText($text, $count+1);
	}
}