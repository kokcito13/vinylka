<?php

class Admin_RecommendController extends Zend_Controller_Action {

    public function preDispatch() {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array(), 'admin-login'));
        else
            $this->view->blocks = (object) array('menu' => true);
        $this->view->add = false;
        $this->view->back = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
        $this->view->headTitle()->append('Список рекомендованых');
    }

    public function indexAction() {
        $this->view->add = (object) array(
                    'link' => $this->view->url(array(), 'admin-recommend-add'),
                    'alt' => 'Добавить Рекомендованое',
                    'text' => 'Добавить Рекомендованое'
        );

        $this->view->breadcrumbs->add('Список рекомендованых', '');
        $this->view->headTitle()->append('Список рекомендованых');
        $this->view->recommends = Application_Model_Kernel_Recommend::getList();
    }

    public function addAction() {

        $this->view->breadcrumbs->add('Добавить рекомендацию', '');
        $this->view->headTitle()->append('Добавить');

        $this->view->langs = Kernel_Language::getAll();
        $this->view->tinymce = true;
        $this->view->back = true;
        $this->view->edit = false;
        $this->view->idPhoto1 = 0;

        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            try {
                
                $this->view->idPhoto1 = (int) $data->idPhoto1;
                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }

                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->recommend = new Application_Model_Kernel_Recommend(null, $this->view->idPhoto1, null, Application_Model_Kernel_Aphorism::STATUS_SHOW, 0);
                $this->view->recommend->setContentManager($contentManager);
                $this->view->recommend->save();
                $this->_redirect($this->view->url(array(), 'admin-recommend-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
    }

    public function editAction() {
        $this->view->headTitle()->append('Редактировать рекомендацию');
		$this->_helper->viewRenderer->setScriptAction('add');
        $this->view->tinymce = true;
        $this->view->edit = true;
        
		$this->view->langs = Kernel_Language::getAll();
		$this->view->recommend = Application_Model_Kernel_Recommend::getById((int)$this->_getParam('idRecommend'));
		$this->view->idPhoto1 = $this->view->recommend->getIdPhoto1();
        
        $getContent = $this->view->recommend->getContentManager()->getContent();
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
                        $this->view->recommend->getContentManager()->setLangContent($lang->getId(), $fields);
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
                
                $this->view->idPhoto1 = (int) $data->idPhoto1;

                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

                $this->view->recommend->setIdPhoto1($this->view->idPhoto1);
                
				$this->view->recommend->save();
    			$this->_redirect($this->view->url(array(), 'admin-recommend-index'));
    		} catch(Application_Model_Kernel_Exception $e) {
				$this->view->ShowMessage($e->getMessages());
			} catch(Exception $e) {
				$this->view->ShowMessage($e->getMessage());
			}	
		} else {
            $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
			$_POST['content'] = $this->view->recommend->getContentManager()->getContents();
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
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            switch (intval($data->type)) {
   				case 1://change status
   					$this->view->recommend = Application_Model_Kernel_Recommend::getById((int) $data->id);
                    if ($this->view->recommend->getStatus() == 1)
                        $this->view->recommend->hide();
                    else
                        $this->view->recommend->show();
   				break;
   				case 2: //delete
   					$this->view->recommend = Application_Model_Kernel_Recommend::getById((int) $data->id);
   					$this->view->recommend->delete();
   				break;
   			}
            echo 1;
            exit();
        }
        echo 0;
        exit();
    }

    public function mainchangeAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();

            $this->view->project = Application_Model_Kernel_Project::getById((int) $data->idProject);
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

    public function changepositionprojectAction() {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            Application_Model_Kernel_Project::changePosition((int) $data->id, (int) $data->val);
            echo 1;
        }
    }

}