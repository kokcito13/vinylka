<?php

class AjaxController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }

    public function getAjaxListAction()
    {
        $result = array();
        $types = array();
        $where = '';
        if ($this->getRequest()->isGet()) {
            $data = (object)array_merge($this->getRequest()->getPost(), $_GET);
            $idCat = (int)$this->_getParam('idCat');

            if ($data->types) {
                $types = explode('-', $data->types);
                $productsIds = Application_Model_Kernel_Category::getProductsIdsByTypes($types);
                if (empty($productsIds)) {
                    $productsIds[] = 0;
                }
                $where = 'products.idProduct IN (' . join(',', $productsIds) . ')';
            }

            if ($idCat) {
                $this->view->page = Application_Model_Kernel_Cat::getById($idCat);
                $ids = $this->view->page->getListIdProductByCategory();
                if (!empty($productsIds)) {
                    $ids = array_intersect($ids, $productsIds);
                    if (empty($ids)) {
                        $ids[] = 0;
                    }
                }
                $where = 'products.idProduct IN (' . join(',', $ids) . ')';
            }

            if (isset($data->status) && $data->status) {
                if (strlen($where) > 2)
                    $where .= ' AND ';
                $where .= ' `products`.`productStatus` = '.$data->status;
            }
            
            $this->view->publicList = Application_Model_Kernel_Product::getList(false, false, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, $data->page, Application_Model_Kernel_Product::ITEM_ON_PAGE, Application_Model_Kernel_Product::ITEM_ON_PAGE, true, $where);

            $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
            $viewPg = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
            $view->items = $this->view->publicList->data;
            $viewPg->items = $this->view->publicList;

            $result['html'] = $view->render('block/list_items.phtml');

            $result['html'] .= $viewPg->render('block/ajax_paginator.phtml');

            if (count($view->items) == 0) {
                $result['html'] = 'Наклейки не найдены';
            }

            $result['success'] = true;
        }
        else {
            $result['error'] = 'not good method';
        }

        echo json_encode((object)$result);
        exit;
    }

    public function addOrderAction()
    {
        $response = array();
        $idProduct = (int)$this->_getParam('idProduct');
        if ($this->getRequest()->isPost() || true) {
            $data = (object)array_merge($this->getRequest()->getPost(), $_GET);

            if (empty($data->name)) {
                $response['error']['Ваше имя'] = 'Пустое поле!';
            }

            if (empty($data->mob)) {
                $response['error']['Контактный телефон'] = 'Пустое поле!';
            }

            if (!empty($data->last_name)) {
                $response['error']['Вы'] = ' - робот!';
            }

            if (!isset($response['error']) || empty($response['error'])) {

                $delivery = '';
                $ids = array();
                $product = Application_Model_Kernel_Product::getById($idProduct);
                $productContent = $product->getContent()->getFields();
                $categories = $product->getListCategoryByIdProduct();

                $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));

                foreach ($product->getDeliveryTypes() as $k=>$v) {
                    if ($k == $data->delivery) {
                        $delivery = $v;
                    }
                }

                foreach ($categories as $category) {
                    $ids[] = $category->idCategorie;
                }

                $view->data = $data;
                $view->delivery = $delivery;
                $view->product = $product;
                $view->productContent = $productContent;

                $html = $view->render('block/order_mail.phtml');

                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($html);
                $mail->setFrom('manager@vinylka.com.ua', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->addTo('oklosovich@gmail.com', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->addTo('grygorenko.viktoria@gmail.com', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->addTo('glyuda@gmail.com', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->setSubject('Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->send();

                $response['success'] = true;

                $order = new Application_Model_Kernel_Order(null, $idProduct, $product->getUserPrice(), $data->name, $data->mob, $data->text,
                                                   $product->getPrice(), $delivery, $ids,
                                                   array(), time());
                $order->save();
            }

            echo json_encode($response);

            return false;
        }
    }

    public function individualOrderAction()
    {
        $response = array();
        if ($this->getRequest()->isPost() || true) {
            $data = (object)array_merge($this->getRequest()->getPost(), $_GET);

            if (empty($data->name)) {
                $response['error']['Ваше имя'] = 'Пустое поле!';
            }

            if (empty($data->mob)) {
                $response['error']['Контактный телефон'] = 'Пустое поле!';
            }

            if (empty($data->image)) {
                $response['error']['Картинка'] = 'Незагружена!';
            }

            if (!empty($data->last_name)) {
                $response['error']['Вы'] = ' - робот!';
            }

            if (!isset($response['error']) || empty($response['error'])) {
                $response['success'] = true;
            }

            if (!isset($response['error']) || empty($response['error'])) {
                $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
                $view->data = $data;

                $html = $view->render('block/order_ind_mail.phtml');

                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($html);
                $mail->setFrom('manager@vinylka.com.ua', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->addTo('oklosovich@gmail.com', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->addTo('grygorenko.viktoria@gmail.com', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->addTo('glyuda@gmail.com', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->setSubject('Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->send();

                $response['success'] = true;

                $order = new Application_Model_Kernel_Order(null, 0, 0, $data->name, $data->mob, $data->text,
                                                            0, 0, array(),
                                                            array(), time());
                $order->save();
            }

            echo json_encode($response);

            return false;
        }
    }
}
