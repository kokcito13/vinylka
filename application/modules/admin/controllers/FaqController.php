<?php

class Admin_FaqController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array (), 'admin-login'));
        else
            $this->view->blocks = (object)array ('menu' => true);
        $this->view->add         = false;
        $this->view->back        = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page        = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
        $this->view->headTitle()->append('Список faq');
    }

    public function indexAction()
    {
        $this->view->add = (object)array (
            'link' => $this->view->url(array (), 'admin-faq-add'),
            'alt'  => 'Добавить faq',
            'text' => 'Добавить faq'
        );

        $this->view->page = (int)$this->_getParam('page');

        $this->view->breadcrumbs->add('Список faq', '');
        $this->view->headTitle()->append('Список faq');
        $this->view->projectList = Application_Model_Kernel_Faq::getList(false, false, true, true, false, false, $this->view->page, 15, false, true, false);

    }

    public function addAction()
    {
        $this->view->langs    = Kernel_Language::getAll();
        $this->view->idPage   = null;
        $this->view->tinymce  = true;
        $this->view->back     = true;
        $this->view->edit     = false;
        $this->view->idPhoto1 = 0;

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $url = new Application_Model_Kernel_Routing_Url("/" . $data->url);

                $this->view->idPhoto1 = (int)$data->idPhoto1;
                $this->view->photo1   = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams();
                $route         = new Application_Model_Kernel_Routing(null, Application_Model_Kernel_Routing::TYPE_ROUTE, '~faq', 'default', 'faq', 'show', $url, $defaultParams, Application_Model_Kernel_Routing::STATUS_ACTIVE);

                $content = array ();
                $i       = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->product = new Application_Model_Kernel_Faq(null, $this->view->idPhoto1, null, null, null, time(), Application_Model_Kernel_Page_ContentPage::STATUS_SHOW, 0);

                $this->view->product->setContentManager($contentManager);
                $this->view->product->setRoute($route);
                $this->view->product->validate($data);
                $this->view->product->save();

                $this->_redirect($this->view->url(array ('page' => 0), 'admin-faq-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }

        $this->view->breadcrumbs->add('Добавить faq', '');
        $this->view->headTitle()->append('Добавить');
    }

    public function editAction()
    {
        $fields            = array ();
        $this->view->langs = Kernel_Language::getAll();
        $this->view->back  = true;
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->tinymce  = true;
        $this->view->edit     = true;
        $this->view->product  = Application_Model_Kernel_Faq::getById((int)$this->_getParam('id'));
        $this->view->idPhoto1 = $this->view->product->getIdPhoto1();

        $getContent = $this->view->product->getContentManager()->getContent();
        foreach ($getContent as $key => $value) {
            $getContent[$key]->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($getContent[$key]->getId()));
        }
        $this->view->idPage = $this->view->product->getIdPage();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->product->getRoute()->setUrl("/" . $data->url);
                foreach ($this->view->langs as $lang) {
                    $value     = $getContent[$lang->getId()];
                    $oldFields = $value->getFields();
                    $idContent = $value->getId();
                    foreach ($data->content[$lang->getId()] as $keyLang => $valueLang) {
                        $valueFields = $oldFields[$keyLang];
                        if (!isset($valueFields)) {
                            $field = new Application_Model_Kernel_Content_Fields(null, $idContent, $keyLang, $valueLang);
                            $field->save();
                            continue;
                        }
                        if ($valueLang !== $valueFields->getFieldText()) {
                            $fields[] = new Application_Model_Kernel_Content_Fields($valueFields->getIdField(), $valueFields->getIdContent(), $valueFields->getFieldName(), $valueLang);
                        }
                    }

                    $this->view->product->getContentManager()->setLangContent($lang->getId(), $fields);
                    $fields = array ();
                }

                if (count($data->content) > count($getContent)) {
                    foreach ($getContent as $value) {
                        $idContentPack = $value->getIdContentPack();
                        unset($data->content[$value->getIdLang()]);
                    }
                    foreach ($data->content as $key => $value) {
                        $content = new Application_Model_Kernel_Content_Language(null, $key, $idContentPack);
                        foreach ($value as $k => $v) {
                            $content->setFields($k, $v);
                        }
                        $content->save();
                    }
                }
                $this->view->idPhoto1 = (int)$data->idPhoto1;
                $this->view->photo1   = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
                $this->view->product->setIdPhoto1($this->view->idPhoto1);
                $this->view->product->save();

                $this->_redirect($this->view->url(array ('page' => 1), 'admin-faq-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
            $_POST['url']       = mb_substr(Application_Model_Kernel_Routing::getById($this->view->product->getIdRoute())->getUrl(), 1);
            $_POST['content']   = $this->view->product->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
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

            $faq = Application_Model_Kernel_Faq::getByIdPage((int)$data->id);
            if ($data->type == 2) {
                $faq->delete();
            } else {
                $faq->show();
                $faq->save();
            }
            exit(0);
        }
        exit(1);
    }
}