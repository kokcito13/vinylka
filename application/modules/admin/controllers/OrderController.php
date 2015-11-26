<?php

class Admin_OrderController extends Zend_Controller_Action
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
        $this->view->headTitle()->append('Список заказов');
    }

    public function indexAction()
    {
        $this->view->page = (int)$this->_getParam('page');

        $this->view->add = (object)array(
            'link' => $this->view->url(array(), 'admin-order-add'),
            'alt'  => 'Добавить заказ',
            'text' => 'Добавить заказ'
        );

        $this->view->breadcrumbs->add('Список заказов', '');
        $this->view->headTitle()->append('Список заказов');
        $this->view->orders = Application_Model_Kernel_Order::getList($this->view->page, 30, (boolean)$this->_getParam('stat'));
    }

    public function editAction()
    {
        $this->view->back = true;
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->edit = true;
        $this->view->idOrder = (int)$this->_getParam('id');

        $this->view->order = Application_Model_Kernel_Order::getById($this->view->idOrder);

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->order->setStatus($data->status);
                $this->view->order->setText($data->text);
                $this->view->order->setEmail($data->email);
                $this->view->order->save();

                $this->_redirect($this->view->url(array('page' => 1), 'admin-order-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }

        $this->view->breadcrumbs->add('Редактировать', '');
        $this->view->headTitle()->append('Редактировать');
    }

    public function addAction()
    {
        $this->view->back = true;
        $this->view->edit = false;

        $this->view->order = new Application_Model_Kernel_Order(null, 0, 0, '', '', '', '0', 'Новая почта');
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {

                $this->view->order->setName($data->name);
                $this->view->order->setPhone($data->phone);
                $this->view->order->setDeliveryType($data->delivery);
                $this->view->order->setUserPrice($data->userPrice);
                $this->view->order->setStatus($data->status);
                $this->view->order->setText($data->text);
                $this->view->order->setEmail($data->email);

                $this->view->order->save();

                $this->_redirect($this->view->url(array('page' => 1), 'admin-order-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }

        $this->view->breadcrumbs->add('Редактировать', '');
        $this->view->headTitle()->append('Редактировать');
    }
}