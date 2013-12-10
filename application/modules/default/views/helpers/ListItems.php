<?php

class Zend_View_Helper_ListItems
{
    public function ListItems($array)
    {
        $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
        $view->items = $array;

        return $view->render('block/list_items.phtml');
    }
}