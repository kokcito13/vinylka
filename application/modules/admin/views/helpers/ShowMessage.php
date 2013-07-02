<?php
/**
 * Zend_View_Helper_ShowMessage
 *
 * Show PopUp helper
 *
 * @author vlad <vlad.melanitski@gmail.com>
 * @version 1.0
 */
	class Zend_View_Helper_ShowMessage {
		
		/**
		 * @name ShowMessage
		 * @access public
		 * 
		 * display error popup with errors
		 * 
		 * @param array|object|string $Message
		 * @param bool $show
		 * @return void
		 */
		public function ShowMessage($Message,$show = true) {
			?><div class="PopUpBg alpha60 <? if (!$show) { ?>hide<? } ?>">
				<div class="errorPopUp">
					<div class="PopUpHeader">
						<span>Уведомление</span>
						<a href="javascript:void(0)" title="Закрыть"><img src="/static/admin/images/closePopUp.png"/></a>
					</div>
					<div class="PopUpBody"><?php
			switch(gettype($Message)) {
				case 'array':
				case 'object':
					$this->parseArray($Message);
				break;
				case 'string': {
					?><ul><li><span>&nbsp;</span><?=$Message;?></li></ul><?php	
				} break;
			}
			?></div>
				</div>
			</div><?php
		}
		/**
		 * @name parseArray
		 * @access protected
		 * 
		 * display messages
		 * 
		 * @param array|object $Message
		 * @return void
		 */
		protected function parseArray($Message) {
			?><ul><?php
				foreach ($Message as $item) :
					?><li><span>&nbsp;</span><?=$item;?></li><?php
				endforeach;
			?></ul><?php	
		}
	}
?>