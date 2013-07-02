<?php
/**
 * Zend_View_Helper_AutoComplete
 *
 * AutoComplete helper
 *
 * @author vlad <vlad.melanitski@gmail.com>
 * @version 1.0
 */
class Zend_View_Helper_AutoComplete {
	
	/**
	 * @name AutoComplete
	 * @access public
	 * 
	 * echo AutoComplete js with serach words, and wake redirect on press enter or click for world
	 * 
	 * @param string $inputId
	 * @param array $completeArray
	 * @param string $curentUrl
	 * @param string $ReplaceWord
	 * @return void
	 */
	public function AutoComplete($inputId, $completeArray, $curentUrl, $ReplaceWord, array $searchWords) {
		$view = Zend_Layout::getMvcInstance()->getView();
		$view->headScript()->captureStart();
		?>
		$(function() {
			var availableTags = [<?=$this->geatherAvailableTags($completeArray, $searchWords);?>];
			$("#<?=$inputId;?>").autocomplete({
				source: availableTags
			});
		});<?php
		$this->makeDocumentReady($inputId,$curentUrl,$ReplaceWord);
		$view->headScript()->captureEnd();
	}
	
	/**
	 * @name geatherAvailableTags
	 * @access protected
	 * 
	 * make string from array of search words 
	 * 
	 * @param array $completeArray 
	 * @return string
	 */
	protected function geatherAvailableTags($completeArray, $searchWords) {
		$i = 1;
		$count = count($completeArray);
		$availableTags = '';
		foreach ($completeArray as $item) {
			$countWords = sizeof($item);
			$item = (object)$item;
			$j = 1;
			foreach ($searchWords as $word) {
				if ($j != $countWords) 
					$availableTags.=',';
				$availableTags .= '"' . addslashes($item->{$word}) . '"';
				$j++;
			}
			if ($i != $count)
				$availableTags.=',';
			$i++;
		}
		return $availableTags; 
	}
	
	/**
	 * @name makeDocumentReady
	 * @access protected
	 * 
	 * echo document ready for curent input search box
	 * 
	 * @param string $inputId
	 * @param string $curentUrl
	 * @param string $ReplaceWord
	 * @return void
	 */
	protected function makeDocumentReady($inputId,$curentUrl,$ReplaceWord) {
		?>$(document).ready(function(){
			$("#<?=$inputId;?>" ).bind( "autocompleteclose", function(event, ui) {
				window.location.href = '<?=$curentUrl;?>'.replace("<?=$ReplaceWord;?>", encodeURIComponent(this.value));
			}).keypress(function(event) {
				if (event.keyCode == 13) {
					window.location.href = '<?=$curentUrl;?>'.replace("<?=$ReplaceWord;?>", encodeURIComponent(this.value));
				}
			});
		});<?php
	}
}