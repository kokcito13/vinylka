<?php

class Zend_View_Helper_ShowCommentItem {

    public function ShowCommentItem( $commentId, $comments ){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
        $view->parentComments = array();
        foreach( $comments as $comment ){
            if( $commentId == $comment->getParentIdComment() ){
                $view->parentComments[] = $comment;
            }
        }
        return $view->render('block/comment_item.phtml');
    }
}