<?php

class PublicController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->categories = Application_Model_Kernel_Category::getListParent();

    }

    public function showAction()
    {

        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->product = Application_Model_Kernel_Product::getByIdPage($this->view->idPage);
        $this->view->contentPage = $this->view->product->getContent()->getFields();
        $catIds = $this->view->product->getListCategoryByIdProduct();

        $this->view->menu = 'main';
        if (!empty($catIds)) {
            $this->view->category = Application_Model_Kernel_Cat::getById($catIds[0]->idCategorie);
            $this->view->menu = $this->view->category->getRoute()->getUrl();
        }

        $where = 'products.idProduct < '.$this->view->product->getIdProduct(); //IN (' . join(',', $ids) . ')';
        $this->view->publicList = Application_Model_Kernel_Product::getList('products.idProduct', 'DESC', true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, false, false, 4, true, $where);

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }
}