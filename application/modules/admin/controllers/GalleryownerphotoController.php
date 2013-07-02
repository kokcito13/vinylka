<?php

class Admin_GalleryownerphotoController extends Zend_Controller_Action {

    public function preDispatch() {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array(), 'admin-login'));
        else
            $this->view->blocks = (object) array('menu' => true);
        $this->view->add = false;
        $this->view->back = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;
        $this->view->headTitle()->append('Список фотографий');
    }

    public function indexAction() {
        $this->view->add = (object) array(
                    'link' => $this->view->url(array(), 'admin-galleryowner-photo-add'),
                    'alt' => 'Добавить Фотографию',
                    'text' => 'Добавить Фотографию'
        );
        $this->view->breadcrumbs->add('Список фотографий', '');
        $this->view->headTitle()->append('Список фотографий');
        $this->view->galleryPhotos = Application_Model_Kernel_Galleryowner::getList(false, Application_Model_Kernel_Galleryowner::TYPE_PHOTO);
    }

    public function addAction() {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->idPage = null;
        $this->view->back = true;
        $this->view->idPhoto = 0;
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            try {

                $this->view->idPhoto = (int) $data->idPhoto;
                $this->view->photo = Application_Model_Kernel_Photo::getById($this->view->idPhoto);

                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->galleryPhoto = new Application_Model_Kernel_Galleryowner(null, null, $this->view->idPhoto, Application_Model_Kernel_Galleryowner::TYPE_PHOTO, 1, 0, 0, time());
                $this->view->galleryPhoto->setContentManager($contentManager);
                $this->view->galleryPhoto->save();

                $this->_redirect($this->view->url(array(), 'admin-galleryowner-photo-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
        $this->view->breadcrumbs->add('Добавить Фотографию', '');
        $this->view->headTitle()->append('Добавить');
    }

    public function editAction() {
        $this->view->langs = Kernel_Language::getAll();
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->edit = true;
        $this->view->galleryPhoto = Application_Model_Kernel_Galleryowner::getById((int) $this->_getParam('idGalleryowner'));

        $getContent = $this->view->galleryPhoto->getContentManager()->getContent();
        foreach ($getContent as $key => $value)
            $getContent[$key]->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($getContent[$key]->getId()));

        $this->view->idPhoto = $this->view->galleryPhoto->getIdPhoto();
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            try {

                $this->view->idPhoto = (int) $data->idPhoto;
                $this->view->photo = Application_Model_Kernel_Photo::getById($this->view->idPhoto);

                $i = 0;
                foreach ($this->view->langs as $lang) {
                    foreach ($data->content[$lang->getId()] as $keyLang => $valueLang) {
                        foreach ($getContent as $key => $value) {
                            if ($value->getIdLang() == $lang->getId()) {
                                foreach ($value->getFields() as $keyField => $valueFields) {
                                    if ($keyLang === $valueFields->getFieldName()) {
                                        if ($valueLang !== $valueFields->getFieldText()) {
                                            $fields[] = new Application_Model_Kernel_Content_Fields($valueFields->getIdField(), $valueFields->getIdContent(), $valueFields->getFieldName(), $valueLang);
                                        } else {
                                            break;
                                        }
                                    } else if (!isset($getContent[$keyLang])) {
                                        $field = new Application_Model_Kernel_Content_Fields(null, $idContent, $keyLang, $valueLang);
                                        $field->save();
                                    }
                                }
                            }
                        }
                    }
                    if (isset($getContent[$lang->getId()])) {
                        $this->view->galleryPhoto->getContentManager()->setLangContent($lang->getId(), $fields);
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
                $this->view->galleryPhoto->setIdPhoto($this->view->idPhoto);
                $this->view->galleryPhoto->save();

                $this->_redirect($this->view->url(array(), 'admin-galleryowner-photo-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $this->view->photo = Application_Model_Kernel_Photo::getById($this->view->idPhoto);
            $_POST['content'] = $this->view->galleryPhoto->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }
        }
        $this->view->breadcrumbs->add('Редактировать', '');
        $this->view->headTitle()->append('Редактировать');
    }

    public function deletAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        $this->view->galleryPhoto = Application_Model_Kernel_Galleryowner::getById((int) $this->_getParam('idGalleryowner'));
        $this->view->galleryPhoto->delete();
        $this->_redirect($this->view->url(array(), 'admin-galleryowner-photo-index'));
    }

    public function statusAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            $page = Application_Model_Kernel_Galleryowner::getById((int) $data->id);

            switch ((int) $data->type) {
                case 1://change status
                    switch ((int) $page->getStatus()) {
                        case Application_Model_Kernel_Galleryowner::STATUS_SHOW:
                            $page->hide();
                            break;
                        case Application_Model_Kernel_Galleryowner::STATUS_HIDE:
                            $page->show();
                            break;
                    }
                    break;
                case 2: //delete
                    $page->delete();
                    break;
            }
        }
    }

    public function toppicAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object) $this->getRequest()->getPost();
            $page = Application_Model_Kernel_Galleryowner::getById((int) $data->id);

            switch ((int) $page->getGalleryOwnerWeek()) {
                case Application_Model_Kernel_Galleryowner::STATUS_SHOW:
                    $page->setGalleryOwnerWeek(Application_Model_Kernel_Galleryowner::STATUS_HIDE);
                    break;
                case Application_Model_Kernel_Galleryowner::STATUS_HIDE:
                    $page->setGalleryOwnerWeek(Application_Model_Kernel_Galleryowner::STATUS_SHOW);
                    break;
            }
            $page->save();
        }
    }

}