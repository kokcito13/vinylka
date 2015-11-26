<?php

class Admin_BuyerController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array(), 'admin-login'));
        else
            $this->view->blocks = (object)array('menu' => true);
        $this->view->add = false;
        $this->view->back = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
        $this->view->headTitle()->append('Список покупателей');
    }

    public function indexAction()
    {
//        $this->view->add = (object)array(
//            'link' => $this->view->url(array(), 'admin-order-add'),
//            'alt'  => 'Добавить заказ',
//            'text' => 'Добавить заказ'
//        );

        $this->view->breadcrumbs->add('Список покупателей', '');
        $this->view->headTitle()->append('Список покупателей');

        $buyers = Application_Model_Kernel_Buyer::getList();

        $this->view->buyers = $buyers;
    }

    public function showAction()
    {
        $this->view->back = true;
        $phone = $this->_getParam('id');

        $buyer = Application_Model_Kernel_Buyer::getByPhone($phone);

        $this->view->buyer = $buyer;

        $this->view->breadcrumbs->add('Просмотыр заказов', '');
        $this->view->headTitle()->append('Просмотыр заказов');
    }

}