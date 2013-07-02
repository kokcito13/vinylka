<?php

class Admin_PageController extends Zend_Controller_Action {

    public function preDispatch() {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array(), 'admin-login'));
        else
            $this->view->blocks = (object) array('menu' => true);
        $this->view->add = false;
        $this->view->back = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
        $this->view->breadcrumbs->add('Список основных страниц', $this->view->url(array('page' => $this->view->page), 'admin-page-index'));
        $this->view->headTitle()->append('Текстовые страницы');
    }

    public function indexAction() {
        $this->view->pageList = Application_Model_Kernel_Page_ContentPage::getList('pages.idPage', 'DESC', true, true, false, false, (int) $this->_getParam('page'), 20, false);
        $this->view->allPages = Application_Model_Kernel_Page_ContentPage::getList(false, false, true, false, false, false, false, false, false);
    }

    public function addAction() {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->idPage = null;
        $this->view->tinymce = true;
        $this->view->back = true;
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            try {
                $url = new Application_Model_Kernel_Routing_Url($data->url);
                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams();
                $route = new Application_Model_Kernel_Routing(null, Application_Model_Kernel_Routing::TYPE_ROUTE, '~page', 'default', 'page', 'show', $url, $defaultParams, Application_Model_Kernel_Routing::STATUS_ACTIVE);
                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $key => $value) {
                        $content[$i]->setFields($key, $value);
                    }
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);
                $this->view->page = new Application_Model_Kernel_Page_ContentPage(null, null, null, null, time(), Application_Model_Kernel_Page::STATUS_SHOW, 0);
                $this->view->page->setContentManager($contentManager);
                $this->view->page->setRoute($route);
                //$this->view->page->validate();
                $this->view->page->save();
                $this->_redirect($this->view->url(array(), 'admin-page-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
        $this->view->breadcrumbs->add('Добавить страницу', '');
        $this->view->headTitle()->append('Добавить');
    }

    public function editAction() {
        $this->view->langs = Kernel_Language::getAll();
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->tinymce = true;
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getById(intval($this->_getParam('idPage')));
        $this->view->idPage = $this->view->page->getIdPage();
        $getContent = $this->view->page->getContentManager()->getContent();
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            try {
                $this->view->page->getRoute()->setUrl($data->url);
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
                        $this->view->page->getContentManager()->setLangContent($lang->getId(), $fields);
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
                
                //$this->view->page->validate();
                $this->view->page->save();
                $this->_redirect($this->view->url(array(), 'admin-page-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $_POST['url'] = $this->view->page->getRoute()->getUrl();
            $_POST['content'] = $this->view->page->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if( isset($_POST['content'][$lang->getId()]) )
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }
        }
        $this->view->breadcrumbs->add('Редактировать страницу', '');
        $this->view->headTitle()->append('Редактировать');
    }

    public function statusAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            $page = Application_Model_Kernel_Product::getByIdPage( (int)$data->id ) ;
            switch ( (int)$data->type ) {
                case 1://change status
                    switch ( (int)$page->getStatus() ) {
                        case Application_Model_Kernel_Page::STATUS_SHOW:
                            $page->changeStatus( $page->getIdPage(), Application_Model_Kernel_Page::STATUS_HIDE );
                            break;
                        case Application_Model_Kernel_Page::STATUS_HIDE:
                            $page->changeStatus( $page->getIdPage(), Application_Model_Kernel_Page::STATUS_SHOW );
                            break;
                    }
                    break;
                case 2: //delete
                    $page->delete();
                    break;
            }
        }
    }

    public function changepositionAction(){
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
                if( $data->sweet === "false"){
                    $mes = Application_Model_Kernel_Page::changePosition($key, (1000-$i) )|$mes;
                } else {
                    $mes = Application_Model_Kernel_Project::changePosition($key, (1000-$i) )|$mes;
                }
                $i++;
            }
            echo $mes;
        }
        
    }
    
    public function changepositiontrueAction(){
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        if ($this->getRequest()->isPost()){
            $data = (object) $this->getRequest()->getPost();
            Application_Model_Kernel_Page::changePosition((int)$data->id, (int)$data->val );
            echo 1;
        }
    }
}