<?php
/**
 * Zend_View_Helper_PageTree
 *
 * PageTree helper
 *
 * @author vlad <vlad.melanitski@gmail.com>
 * @version 1.0
 */
class Zend_View_Helper_BuildTree {

	protected $class;
	protected $helperView;
	
	public function __construct() {
		$this->helperView = Zend_Layout::getMvcInstance()->getView();
	}
	
	/**
	 * @name BuildTree
	 * @access public
	 * @param array $array
	 * @param string $class //grey || light
	 * @param int $level
	 * @param string $editUrl
	 * @return void
	 */
	public function BuildTree(array $array, $class, $level, $editUrl) {
		++$level;
		$i = 0;
		$count = count($array);
		?><ul class="<? if ($level === 1) { 
			?>tree<? } 
		?>"><?
		if ($array) {
			foreach($array as $row) :
				$i++;
				($class === 'light') ? $class = 'grey' : $class = 'light'; //color palet
				$last = ($i == $count) ? 'last' : '';
				$first = ($i === 1) ? 'first' : '';
				?><li class="level_<?=$level;?> <?=$class?> <?=$last?> <?=$first?> id_<?=$row->getId()?>" id="<?=$row->getId()?>" >
					<a href="/admin/type/edit/<?=$row->getId()?><?//=$this->helperView->url(array('idСategory' => (int)$row->getId()), $editUrl)?>" class="edit">
						<img src="/static/admin/images/edit.png" alt="Edit" title="Редактировать" width="16" height="16" />
					</a>
					<a href="javascript:void(0);" class="text"><?=Application_Model_Kernel_Content_Fields::getFieldByIdContentAndNameField($row->getContent()->getId(), 'contentName')->getFieldText()?><?//=$row->getContent()->getContentName()?></a>
					
					<a href="javascript:changeStatus(<?=$row->getId()?>, TYPE_DELETE);" class="deletePage arrow">
						<img class="<?=$row->getId()?>" src="/static/admin/images/icon_delete.gif" alt="" width="9" height="8" />
					</a>
					
					<a href="javascript:changeStatus(<?=$row->getId()?>, TYPE_STATUS);" class="<?=$row->getStatus()?>">
						<img class="<?=$row->getStatus()?> page_status_<?=$row->getId()?>" src="/static/admin/images/show_<?=$row->getStatus()?>.png" alt="" width="15" height="15">
					</a>
					</li>
			<?php endforeach;?>
			</ul>
			<?php
		}
	}
}
