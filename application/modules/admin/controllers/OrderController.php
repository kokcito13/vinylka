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

        $this->view->breadcrumbs->add('Список заказов', '');
        $this->view->headTitle()->append('Список заказов');
        $this->view->orders = Application_Model_Kernel_Order::getList($this->view->page, 10);
    }

    public function editAction()
    {
        $this->view->back = true;
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->edit = true;
        $this->view->order = Application_Model_Kernel_Order::getById((int)$this->_getParam('id'));

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->order->setStatus($data->status);
                $this->view->order->setText($data->text);
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

    public function statuschangeAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();

            $this->view->project = Application_Model_Kernel_Product::getById((int)$data->idProduct);
            if ($this->view->project->getProductStatusPopular() != 2)
                $this->view->project->setProductStatusPopular(2);
            else
                $this->view->project->setProductStatusPopular(1);
            $this->view->project->save();
            echo 1;
            exit();
        }
        echo 0;
        exit();
    }

    public function mainchangeAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();

            $this->view->project = Application_Model_Kernel_Project::getById((int)$data->idProject);
            if ($this->view->project->getProjectMain() == 1)
                $this->view->project->setProjectMain(0);
            else
                $this->view->project->setProjectMain(1);
            $this->view->project->save();
            echo 1;
            exit();
        }
        echo 0;
        exit();
    }

    public function changepositionprojectAction()
    {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            Application_Model_Kernel_Project::changePosition((int)$data->id, (int)$data->val);
            echo 1;
        }
    }

    public function getBookInfo($isbn, $access_key, $secure_access_key)
    {
        // формируем список параметров запроса
        $fields = array();
        $fields['AWSAccessKeyId'] = $access_key;
        $fields['ItemId'] = $isbn;
        $fields['MerchantId'] = 'All';
        $fields['Operation'] = 'ItemLookup';
        $fields['ResponseGroup'] = 'Request,Large';
        $fields['Service'] = 'AWSECommerceService';
        $fields['Version'] = '2009-01-06';
        $fields['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');

        // сортируем параметры согласно спецификации Amazon API
        ksort($fields);

        $query = array();
        foreach ($fields as $key => $value) {
            $query[] = "$key=" . urlencode($value);
        }

        // подписываем запрос секретным ключом
        $string = "GET\nwebservices.amazon.com\n/onca/xml\n" . implode('&', $query);
        $signed = urlencode(base64_encode(hash_hmac('sha256', $string, $secure_access_key, true)));

        // формируем строку запроса к сервису
        $url = 'http://webservices.amazon.com/onca/xml?' . implode('&', $query) . '&Signature=' . $signed;

        $url = 'http://webservices.amazon.com/onca/xml?
        Service=AWSECommerceService&
        AWSAccessKeyId=' . $access_key . '&
        Operation=ItemLookup&
        ItemId=B00008OE6I
        &Timestamp=' . gmdate('Y-m-d\TH:i:s\Z') . '
        &Signature=' . $signed;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] != '200') return false;

        return $data;
    }
}