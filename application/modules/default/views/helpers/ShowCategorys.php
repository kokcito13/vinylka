<?php

class Zend_View_Helper_ShowCategorys {

    public function ShowCategorys(){
//        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
//
//        $view->category = Application_Model_Kernel_Category::getStructCategories(true);
//        $view->sortList = array();
//        foreach( $view->category  as $category ){
//            $view->sortList[$category->getId()] = count($category->getListIdProductByCategory());
//        }
//
//        arsort ($view->sortList);
//
//        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
//        foreach( $view->blocks as $key=>$value ){
//            $view->blocks[$key] = $value->getContent()->getFields();
//        }
        return Application_Model_Kernel_Cat::getList(false, false, true, true, false, 1, 20, true, false)->data;
    }
}