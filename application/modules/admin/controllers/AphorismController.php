<?php
class Admin_AphorismController extends Zend_Controller_Action {
	
	public function preDispatch(){
		if(!Application_Model_Admin_Admin::isAuthorized())
			$this->_redirect($this->view->url(array(),'admin-login'));
		else
			$this->view->blocks = (object)array('menu' => true);
		$this->view->add = false;
		$this->view->back = false;
		$this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
		$this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
		$this->view->headTitle()->append('Афоризм');
	}

	public function indexAction() {
		$this->view->add = (object) array(
                    'link' => $this->view->url(array(), 'admin-aphorism-add'),
                    'alt' => 'Добавить афоризм',
                    'text' => 'Добавить афоризм'
        );
        
        $this->view->breadcrumbs->add('Мои афоризмы', '');
        $this->view->headTitle()->append('Мои афоризмы');
		$this->view->aphorisms = Application_Model_Kernel_Aphorism::getList();
	}
	
	public function addAction() {
		$this->view->back = true;
		$this->view->breadcrumbs->add('Добавление афоризм');
		$this->view->headTitle()->append('Добавление афоризм');
		$this->view->langs = Kernel_Language::getAll();
		if ($this->getRequest()->isPost()) {
    		$data = (object)$this->getRequest()->getPost();
    		try {

                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->category = new Application_Model_Kernel_Aphorism(null, null, Application_Model_Kernel_Aphorism::STATUS_SHOW, 0);
				$this->view->category->setContentManager($contentManager);
    			$this->view->category->save();
    			$this->_redirect($this->view->url(array(), 'admin-aphorism-index'));
                
			} catch(Application_Model_Kernel_Exception $e) {
				$this->view->ShowMessage($e->getMessages());
			} catch(Exception $e) {
				$this->view->ShowMessage($e->getMessage());
			}	
		}
	}
	
	public function editAction() {
		$this->view->headTitle()->append('Редактировать афоризм');
		$this->_helper->viewRenderer->setScriptAction('add');
		$this->view->langs = Kernel_Language::getAll();
		$this->view->aphorism = Application_Model_Kernel_Aphorism::getById((int)$this->_getParam('idAphorism'));
		
        $getContent = $this->view->aphorism->getContentManager()->getContent();
		foreach ($getContent as $key=>$value) {
			$getContent[$key]->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($getContent[$key]->getId()));
		}
		if ($this->getRequest()->isPost()) {
    		$data = (object)$this->getRequest()->getPost();
    		try {
				$fields = array();
    			$content = array();
    			foreach ($this->view->langs as $lang) {
	    			foreach ($data->content[$lang->getId()] as $keyLang => $valueLang){
                        foreach ($getContent as $key => $value){
                            if( $value->getIdLang() == $lang->getId() ){
                                foreach ($value->getFields() as $keyField => $valueFields){
                                    if ($keyLang === $valueFields->getFieldName()){
                                        if ($valueLang !== $valueFields->getFieldText()){
                                            $fields[] = new Application_Model_Kernel_Content_Fields($valueFields->getIdField(), $valueFields->getIdContent(), $valueFields->getFieldName(), $valueLang);
                                        } else {
                                            break;
                                        }
                                    } else if( !isset($getContent[$keyLang]) ){
                                        $field = new Application_Model_Kernel_Content_Fields(null, $idContent, $keyLang, $valueLang);
                                        $field->save();
                                    }
                                }
                            }
                        }
                    }
                    if( isset($getContent[$lang->getId()]) ){
                        $this->view->aphorism->getContentManager()->setLangContent($lang->getId(), $fields);
                        $fields = array();
                    }
				}
                if( count($data->content) > count($getContent) ){
                    foreach ($getContent as $key => $value){
                        $idContentPack = $value->getIdContentPack();
                        unset($data->content[$value->getIdLang()]);
                    }
                    foreach( $data->content as $key=>$value ){
                        $content = new Application_Model_Kernel_Content_Language(null, $key, $idContentPack);
                        foreach($value as $k=>$v)
                            $content->setFields($k, $v);
                        $content->save();
                    }
                }
                
	   			//$this->view->category->validate();
				$this->view->aphorism->save();
    			$this->_redirect($this->view->url(array(), 'admin-aphorism-index'));
    		} catch(Application_Model_Kernel_Exception $e) {
				$this->view->ShowMessage($e->getMessages());
			} catch(Exception $e) {
				$this->view->ShowMessage($e->getMessage());
			}	
		} else {
			$_POST['content'] = $this->view->aphorism->getContentManager()->getContents();
            foreach ($this->view->langs as $lang){
                if( isset($_POST['content'][$lang->getId()]) )
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }
		}
	}
	
	public function statusAction() {
		$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->disableLayout();
	   	if($this->getRequest()->isPost()) {
			$data = (object)$this->getRequest()->getPost();
			switch (intval($data->type)) {
   				case 1://change status
   					$category = Application_Model_Kernel_Aphorism::getById(intval($data->id));
   					switch (intval($category->getStatus())) {
   						case Application_Model_Kernel_Aphorism::STATUS_SHOW:
   							$category->hide();
   						break;
   						case Application_Model_Kernel_Aphorism::STATUS_HIDE:
   							$category->show();
   						break;
   					}
   				break;
   				case 2: //delete
   					$category = Application_Model_Kernel_Aphorism::getById(intval($data->id));
   					$category->delete();
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