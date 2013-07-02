<?php

class Zend_View_Helper_ShowRecomend {

    public function ShowRecomend(){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
        
        $view->recommends = Application_Model_Kernel_Recommend::getList(true);
        return $view->render('block/recomend.phtml');
    }
}