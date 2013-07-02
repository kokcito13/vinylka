<?php
class Zend_View_Helper_GetBlogPosts {
	
	public function GetBlogPosts() {
		return Application_Model_Kernel_Blog::getList(false, 'blog.createDate', 'DESC', true, false, false, false, false, false)->data;
	}
}