<?php
class Zend_View_Helper_GetCategoriesSelect {

	public function GetCategoriesSelect($id = null) {
		$result = '<select name="idCategory">';
		$parentCategories = Application_Model_Kernel_Category::getStructCategories(true);
		foreach ($parentCategories as $categories) {
			$result .= "<optgroup label='{$categories->getContent()->getContentname()}'>";
			foreach ($categories->getChildrenNodes() as $category) {
				$select = (($id == $category->getId()) ? 'selected="selected"' : '');
				$result .= "<option value='{$category->getId()}' {$select}>{$category->getContent()->getContentname()}</option>";
			}
			$result .= '</optgroup>';
		}
		$result .= '</select>';
		return $result;
	}
	
}