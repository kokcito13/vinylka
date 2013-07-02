<?php
class Zend_View_Helper_CutText {
	
	public function CutText($text, $to = 10, $end = '...') {
		$length = strlen($text);
		return ($length > $to) ? //длина строки подходит
			(in_array(mb_substr($text, $to, 1, 'UTF-8'), array(' ', ','))) ? //ищем пробел или , для обрезки строки
				mb_substr($text, 0, $to, 'UTF-8') . $end
			:
				$this->CutText($text, $to + 1)
		: $text;	
	}
}