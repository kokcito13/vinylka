<?php

class Admin_SiteController extends Zend_Controller_Action
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
        $this->view->headTitle()->append('Настройки страниц');
    }

    public function setingsAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->tinymce = true;
        $this->view->edit = true;
        $this->view->info = Application_Model_Kernel_SiteSetings::getBy();

        $this->view->idPhoto1 = $this->view->info->getIdPhoto1();

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {

                $this->view->idPhoto1 = (int)$data->idPhoto1;

                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

                $this->view->info->setIdPhoto1($this->view->idPhoto1);
                $this->view->info->setRate($data->rate);
                $this->view->info->setMinPrice($data->minPrice);
                $this->view->info->setPhone($data->phone);
                $this->view->info->setEmail($data->email);

                $this->view->info->save();

                $this->_redirect($this->view->url(array(), 'admin-main-page'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {

            $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

        }
        $this->view->breadcrumbs->add('Редактировать', '');
        $this->view->headTitle()->append('Редактировать');
    }


}