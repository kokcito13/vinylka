<?php
class Admin_CommentController extends Zend_Controller_Action {
	
	public function preDispatch(){
		if(!Application_Model_Admin_Admin::isAuthorized())
			$this->_redirect($this->view->url(array(),'admin-login'));
		else
			$this->view->blocks = (object)array('menu' => true);
		$this->view->add = false;
		$this->view->back = false;
		$this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
		$this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
		$this->view->headTitle()->append('Комменты');
	}

	public function indexAction() {
		
        $this->view->breadcrumbs->add('Комменты', '');
        $this->view->headTitle()->append('Комменты');
        $this->view->type = (int)$this->_getParam('type');
        $this->view->ownerId = (int)$this->_getParam('idProduct');
		$this->view->comments = Application_Model_Kernel_Comment::getList(false, 'comments.commentType  = '.$this->view->type.' AND comments.idOwner = '.$this->view->ownerId);
	}
	
	public function showAction() {
		$this->view->headTitle()->append('Комменты');
		$this->view->back = true;
        $this->view->comment = Application_Model_Kernel_Comment::getById((int)$this->_getParam('idComment'));
		$this->view->comment->readComment();
        $this->view->comment->save();
        
	}
	
	public function statusAction() {
		$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->disableLayout();
	   	if($this->getRequest()->isPost()) {
			$data = (object)$this->getRequest()->getPost();
			switch (intval($data->type)) {
   				case 1://change status
   				break;
   				case 2: //delete
   					$comment = Application_Model_Kernel_Comment::getById((int)$data->id);
   					$comment->delete();
   				break;
   			}
   		}
   	}
    
    public function positionAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()){
            $data = (object) $this->getRequest()->getPost();
            $ar = (array)json_decode( $data->ar );
            $i = 0;
            $mes = true;
            foreach( $ar as $key=>$value ){
                if($key == 0)
                    continue;
                $mes = Application_Model_Kernel_Aphorism::changePosition($key, (1000-$i) )|$mes;
                $i++;
            }
            echo $mes;
        }
        
    }

}