<?php
class Admin_CategoryController extends Zend_Controller_Action
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
        $this->view->breadcrumbs->add('Список всех типов', $this->view->url(array('page' => $this->view->page), 'admin-category-type-index'));
        $this->view->headTitle()->append('Типы');
    }

    public function indexAction()
    {
        $this->view->add = true;
        $this->view->category = Application_Model_Kernel_Category::getStructCategories(true);
    }

    public function addAction()
    {
        $this->view->back = true;
        $this->view->breadcrumbs->add('Добавление нового типа');
        $this->view->headTitle()->append('Добавление нового типа');
        $this->view->langs = Kernel_Language::getAll();
        $this->view->parentId = null;
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->parentId = $data->parentId;
                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->category = new Application_Model_Kernel_Category(null, null, null, time(), Application_Model_Kernel_Category::STATUS_SHOW, 0);
                $this->view->category->setContentManager($contentManager);
                $this->view->category->setParentId($data->parentId);
                $this->view->category->setUrl($data->url);
                //$this->view->category->validate();
                $this->view->category->save();

                $this->_redirect($this->view->url(array(), 'admin-category-type-index'));

            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
    }

    public function editAction()
    {
        $this->view->headTitle()->append('Редактировать тип');
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->langs = Kernel_Language::getAll();
        $this->view->id = (int)$this->_getParam('idCategory');
        $this->view->category = Application_Model_Kernel_Category::getById($this->view->id);
        $getContent = $this->view->category->getContentManager()->getContent();
        foreach ($getContent as $key => $value) {
            $getContent[$key]->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($getContent[$key]->getId()));
        }
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->parentId = $data->parentId;
                $fields = array();
                $content = array();
                foreach ($this->view->langs as $lang) {
                    foreach ($data->content[$lang->getId()] as $keyLang => $valueLang) {
                        foreach ($getContent as $key => $value) {
                            if ($value->getIdLang() == $lang->getId()) {
                                foreach ($value->getFields() as $keyField => $valueFields) {
                                    $gContent = $value->getFields();
                                    if ($keyLang === $valueFields->getFieldName()) {
                                        if ($valueLang !== $valueFields->getFieldText()) {
                                            $fields[] = new Application_Model_Kernel_Content_Fields($valueFields->getIdField(), $valueFields->getIdContent(), $valueFields->getFieldName(), $valueLang);
                                        }
                                        else {
                                            break;
                                        }
                                    }
                                    else if (!isset($gContent[$keyLang])) {
                                        $field = new Application_Model_Kernel_Content_Fields(null, $value->getId(), $keyLang, $valueLang);
                                        $field->save();
                                    }
                                }
                            }
                        }
                    }
                    if (isset($getContent[$lang->getId()])) {
                        $this->view->category->getContentManager()->setLangContent($lang->getId(), $fields);
                        $fields = array();
                    }
                }
                if (count($data->content) > count($getContent)) {
                    foreach ($getContent as $key => $value) {
                        $idContentPack = $value->getIdContentPack();
                        unset($data->content[$value->getIdLang()]);
                    }
                    foreach ($data->content as $key => $value) {
                        $content = new Application_Model_Kernel_Content_Language(null, $key, $idContentPack);
                        foreach ($value as $k => $v)
                            $content->setFields($k, $v);
                        $content->save();
                    }
                }

                $this->view->category->setParentId($data->parentId);
                $this->view->category->setUrl($data->url);
                //$this->view->category->validate();
                $this->view->category->save();
                $this->_redirect($this->view->url(array(), 'admin-category-type-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
        else {
            $this->view->parentId = $this->view->category->getParentId();
            $_POST['content'] = $this->view->category->getContentManager()->getContents();
            $_POST['url'] = $this->view->category->getUrl();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }

        }
    }

    public function statusAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            switch (intval($data->type)) {
                case 1: //change status
                    $category = Application_Model_Kernel_Category::getById(intval($data->id));
                    switch (intval($category->getStatus())) {
                        case Application_Model_Kernel_Category::STATUS_SHOW:
                            $category->hide();
                            break;
                        case Application_Model_Kernel_Category::STATUS_HIDE:
                            $category->show();
                            break;
                    }
                    break;
                case 2: //delete
                    $category = Application_Model_Kernel_Category::getById(intval($data->id));
                    $category->deleteProducts();
                    $category->delete();
                    break;
                case 3: //move
                    $category = Application_Model_Kernel_Category::getById(intval($data->curentId));
                    $category->move($data->withId, $data->typeMove);
                    break;
            }
        }
    }

}