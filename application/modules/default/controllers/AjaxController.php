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
        $where = false;
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
            
            $this->view->publicList = Application_Model_Kernel_Product::getList(false, false, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, $data->page, Application_Model_Kernel_Product::ITEM_ON_PAGE, Application_Model_Kernel_Product::ITEM_ON_PAGE, true, $where);

            $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
            $viewPg = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
            $view->items = $this->view->publicList->data;
            $viewPg->items = $this->view->publicList;

            $result['html'] = $view->render('block/list_items.phtml');

            $result['html'] .= $viewPg->render('block/ajax_paginator.phtml');

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

                $info = Application_Model_Kernel_SiteSetings::getBy();

                $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
                $view->data = $data;
                $view->product = Application_Model_Kernel_Product::getById($idProduct);
                $view->productContent = $view->product->getContent()->getFields();

                $html = $view->render('block/order_mail.phtml');

                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($html);
                $mail->setFrom('manager@vinylka.com.ua', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->addTo('manager@vinylka.com.ua', 'Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->setSubject('Заказ товара на '.$_SERVER['SERVER_NAME']);
                $mail->send();

                $response['success'] = true;
            }

            echo json_encode($response);

            return false;
        }
    }
}
