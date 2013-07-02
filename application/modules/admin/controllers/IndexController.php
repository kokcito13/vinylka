<?php
class Admin_IndexController extends Zend_Controller_Action {

	public function preDispatch(){
		$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
		$this->view->blocks = (object)array('menu' => false);
		if(!in_array($routeName,array('admin-login','admin-logout','admin-clear'))) {
			if(!Application_Model_Admin_Admin::isAuthorized())
				$this->_redirect($this->view->url(array(),'admin-login'));
			else
				$this->view->blocks = (object)array('menu' => true);
		}
	}
    
    public function loginAction() {
    	$this->view->headTitle('Вход в Админку');
        if (Application_Model_Admin_Admin::isAuthorized())
    		$this->_redirect($this->view->url(array(),'admin-index'));
    	if ($this->getRequest()->isPost()) {
			$data = (object)$this->getRequest()->getPost();
			if (Application_Model_Admin_Admin::auth($data->login,$data->password))
				$this->_redirect($this->view->url(array(),'admin-index'));
			else
				$this->_redirect($this->view->url(array(),'admin-login'));
		}
    }
    
    public function indexAction() {
    	$this->_redirect($this->view->url(array(), 'admin-page-index'));
    }
    
    public function logoutAction() {
    	Application_Model_Admin_Admin::logout();
    	$this->_redirect($this->view->url(array(),'admin-login'));
    }

}

