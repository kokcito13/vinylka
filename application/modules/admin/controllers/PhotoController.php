<?php

class Admin_PhotoController extends Zend_Controller_Action {

    public function preDispatch() {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array(), 'admin-login'));
        else
            $this->view->blocks = (object) array('menu' => true);
    }

    public function uploadAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        if ( $this->getRequest()->isPost() ) {
            try {
                if (intval($this->_getParam('idPhoto')) === 0)
                    $photo = new Application_Model_Kernel_Photo(null, null, '', '', 0);
                else
                    $photo = Application_Model_Kernel_Photo::getById(intval($this->_getParam('idPhoto')));
                
                $paramsPhoto = $photo->movePhotoToTmpDir( isset($_GET['qqfile'])?$_GET:$_FILES );
                
                $photo->validate($paramsPhoto['tmp']);
                $photo->upload($paramsPhoto['tmp'], $paramsPhoto['name']);
                $photo->save();
                echo json_encode(array(
                    'idPhoto' => $photo->getId(),
                    'path' => $photo->getPath(Application_Model_Kernel_Photo::PREV_IMAGE),
                    'status'=>true
                ));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
    }

}