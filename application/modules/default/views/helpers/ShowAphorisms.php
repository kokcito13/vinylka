<?php

class Zend_View_Helper_ShowAphorisms {

    public function ShowAphorisms(){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
        $view->aphorisms = Application_Model_Kernel_Aphorism::getList(true);
        
//        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
//        foreach( $view->blocks as $key=>$value ){
//            $view->blocks[$key] = $value->getContent()->getFields();
//        }
        return $view->render('block/showAphorisms.phtml');
    }
}