<?php

class Zend_View_Helper_ShowAphorisms {

    public function ShowAphorisms(){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
        $view->aphorisms = Application_Model_Kernel_Aphorism::getList(true);
        
        return $view->render('block/showAphorisms.phtml');
    }
}