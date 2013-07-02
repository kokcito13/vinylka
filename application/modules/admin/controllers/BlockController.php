<?php

class Admin_BlockController extends Zend_Controller_Action {

    public function preDispatch() {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array(), 'admin-login'));
        else
            $this->view->blocks = (object) array('menu' => true);
        $this->view->add = false;
        $this->view->back = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
        $this->view->headTitle()->append('Список Блоков');
    }

    public function indexAction() {
        $this->view->add = (object) array(
                    'link' => $this->view->url(array(), 'admin-block-add'),
                    'alt' => 'Добавить блок',
                    'text' => 'Добавить блок'
        );
        $this->view->breadcrumbs->add('Список Блоков', '');
        $this->view->headTitle()->append('Список Блоков');
        $this->view->blockList = Application_Model_Kernel_Block::getList(true);
    }


    public function addAction() {
        $this->view->back = true;
        $this->view->langs = Kernel_Language::getAll();
        $this->view->tinymce = true;
        $this->view->breadcrumbs->add('Добавить блок', '#');
        if ($this->getRequest()->isPost()) {
            try {
                $data = (object) $this->getRequest()->getPost();
                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $block = new Application_Model_Kernel_Block(null, null, $data->blockName);
                $block->setContentManager($contentManager);
                $block->save();

                $this->_redirect($this->view->url(array(), 'admin-block-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
    }

    public function editAction() {
        $this->view->back = true;
        $this->view->langs = Kernel_Language::getAll();
        $this->view->breadcrumbs->add('Редактировать блок', '#');
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->tinymce = true;
        $block = Application_Model_Kernel_Block::getById($this->_getParam('idBlock'));
        
        $getContent = $block->getContentManager()->getContent();
        foreach ($getContent as $key => $value)
            $getContent[$key]->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($getContent[$key]->getId()));
        
        
        if ($this->getRequest()->isPost()) {
            try {
                $data = (object) $this->getRequest()->getPost();
                foreach ($this->view->langs as $lang){
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
                        $block->getContentManager()->setLangContent($lang->getId(), $fields);
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
                
                $block->setName($data->blockName);
                $block->save();
                $this->_redirect($this->view->url(array('page' => $this->view->page), 'admin-block-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $_POST['content'] = $block->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if( isset($_POST['content'][$lang->getId()]) )
                    foreach ($_POST['content'][$lang->getId()] as $value)
                     $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }
            $_POST['blockName'] = $block->getName();
        }
    }

}