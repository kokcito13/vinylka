<?php
/**
 * View helper for getting parent categorioes options
 * @author melanitsky
 */
class Zend_View_Helper_GetParentCategoryOptions {
	
	/**
	 * @name GetParentCategoryOptions
	 * @access public
	 * @param int $currentId
	 * @return string
	 */
	public function GetParentCategoryOptions($currentId = 0) {
		$currentId = (int)$currentId;
		$categories = Application_Model_Kernel_Category::getCategoriesByParentId(null);
		$return = '';
		foreach ($categories as $category) {
			$selected = ($category->getId() == $currentId) ? 'selected="selected"' : '';
			$return .= "<option value='{$category->getId()}' $selected>".Application_Model_Kernel_Content_Fields::getFieldByIdContentAndNameField($category->getContent()->getId(), 'contentName')->getFieldText()."</option>";
		}
		return $return;
	}
	
}