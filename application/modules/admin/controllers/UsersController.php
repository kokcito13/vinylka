<?php

class Admin_UsersController extends Zend_Controller_Action {

    /**
     * predispatch
     * 
     * @name preDispatch
     * @return void
     */
    public function preDispatch() {
        $this->view->blocks = (object) array('menu' => false);
        if (!Application_Model_Admin_Admin::isAuthorized()) {
            $this->_redirect($this->view->url(array(), 'admin-login'));
        } else {
            $this->view->blocks->menu = true;
        }
        $this->view->headTitle('Пользователи');
    }

    /**
     * Index Action
     *
     * @name indexAction
     * @access public 
     * @return void
     */
    public function indexAction() {
        $this->view->headTitle('Управление пользователями');
        $this->view->orderField = (in_array($this->_getParam('orderField'), array('id', 'login', 'fullName', 'status', 'email', 'roleId'))) ? $this->_getParam('orderField') : 'id';
        $this->view->orderRoute = (in_array($this->_getParam('orderRoute'), array('ASC', 'DESC'))) ? $this->_getParam('orderRoute') : 'DESC';
        $this->view->mirrorOrderRoute = ($this->view->orderRoute == 'ASC') ? 'DESC' : 'ASC';
        $this->view->paginator = Application_Model_Kernel_User::getList($this->_getParam('page'), $this->view->orderField, $this->view->orderRoute);
    }

    /**
     * Добавление пользователя
     *
     * @name addAction
     * @access public 
     * @return void
     */
    public function addAction() {
        $this->view->headTitle('Добавление пользователя');
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            try {
                $user = new Application_Model_Kernel_User(null, 3, null, $data->login, $data->email, $data->fullname);
                $user->save($data->password);
                $this->_redirect($this->view->url(array(), 'admin-users-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->showMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->showMessage($e->getMessage());
            }
        }
    }

    /**
     * Редактирование пользователя
     *
     * @name editAction
     * @access public 
     * @return void
     */
    public function editAction() {
        $this->view->headTitle('Редактирование пользователя');
        $this->view->user = Application_Model_Kernel_User::getById($this->_getParam('id'));
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            try {
                $user = new Application_Model_Kernel_User($this->_getParam('id'), 3, $data->status, $data->login, $data->email, $data->fullname);
                if (empty($data->password))
                    $data->password = null;
                $user->save($data->password);
                $this->_redirect($this->view->url(array(), 'admin-users-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->showMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->showMessage($e->getMessage());
            }
        }
    }
    
    public function deletAction() {
        $this->view->user = Application_Model_Kernel_User::getById($this->_getParam('id'));
        
        $this->view->user->delete();
        
        $this->_redirect($this->view->url(array(), 'admin-users-index'));
   
    }

}
