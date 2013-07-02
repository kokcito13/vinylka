<?php

class CatController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->filter = true;
    }

    public function showAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->pageNum = 1;
        $where = false;

        $this->view->page = Application_Model_Kernel_Cat::getByIdPage($this->view->idPage);
        $this->view->contentPage = $this->view->page->getContent()->getFields();
        $this->view->menu = $this->view->page->getRoute()->getUrl();

        $ids = $this->view->page->getListIdProductByCategory();

        if (empty($ids)) {
            $ids[] = 0;
        }
        $where = 'products.idProduct IN ('.join(',',$ids).')';

        $this->view->publicList = Application_Model_Kernel_Product::getList(false, false, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, 1, Application_Model_Kernel_Product::ITEM_ON_PAGE, Application_Model_Kernel_Product::ITEM_ON_PAGE, true, $where);

        $this->view->text = $this->view->contentPage['content']->getFieldText();
        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }
}