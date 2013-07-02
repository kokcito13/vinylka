<?php

class Zend_View_Helper_ShowComments {

    public function ShowComments($ownerId, $type){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
        $view->id = $ownerId;
        $view->type = $type;
        $view->comments = Application_Model_Kernel_Comment::getList(false, 'comments.commentType  = '.$type.' AND comments.idOwner = '.$ownerId);
        
        return $view->render('block/comments.phtml');
    }
}