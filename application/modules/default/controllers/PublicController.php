<?php

class PublicController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->categories = Application_Model_Kernel_Category::getListParent();
        Zend_Session::start();
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

            $this->view->mainCategory = $this->view->category;
            $this->view->mainCategoryContent = $this->view->category->getContent()->getFields();
        }

        $where = 'products.idProduct < '.$this->view->product->getIdProduct(); //IN (' . join(',', $ids) . ')';
        $this->view->publicList = Application_Model_Kernel_Product::getList('products.idProduct', 'DESC', true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, false, false, 4, true, $where);


        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        if (empty($title)) {
            $title = "Наклейка ".$this->view->contentPage['contentName']->getFieldText(). ' - для макбука с доставкой по Украине в интернет магазинe Vinylka.com.ua.';
        }
        if (empty($description)) {
            $description = $this->my_mb_ucfirst($this->view->contentPage['contentName']->getFieldText()). ' наклейка (виниловая) для макбука (Apple Macbook) или iPad - на Vinylka.com.ua! Покупайте виниловые черные или цветные наклейки на apple девайсы с комфортом! Vinylka.com.ua.';
        }

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $remarketing = new Zend_Session_Namespace('remarketing');
        if ($remarketing->product_id) {
            if ($remarketing->product_id != $this->view->product->getIdProduct()) {
                $mRemarketing = Application_Model_Kernel_Remarketing::getByIp($_SERVER['REMOTE_ADDR']);
                $this->updateCurentProduct($mRemarketing, $remarketing->product_id);
            }
        } else {
            $this->setNewProduct($this->view->product->getIdProduct(), $remarketing->product_id);
        }
        $remarketing->product_id = $this->view->product->getIdProduct();
    }

    public function setNewProduct($product_id)
    {
        $remarketingNewProduct = new Application_Model_Kernel_Remarketing(0, $product_id);
        $remarketingNewProduct
            ->setIp($_SERVER['REMOTE_ADDR'])
            ->save();

        return $remarketingNewProduct;
    }

    public function updateCurentProduct($remarketing, $product_id)
    {
        if (is_null($remarketing))
            $remarketing= $this->setNewProduct($product_id);

        $remarketing->setProductId($product_id);
        $remarketing
            ->update();
    }

    function my_mb_ucfirst($str) {
        $fc = mb_strtoupper(mb_substr($str, 0, 1));
        return $fc.mb_substr($str, 1);
    }
}