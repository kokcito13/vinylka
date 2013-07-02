<?php

class Admin_VideoController extends Zend_Controller_Action {

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
                
                $fileSize = '';

                if (isset($_GET['qqfile'])) {
                    $originalFileName = $_GET['qqfile']; // from FF, Chrome  - comes as GET param

                    $fileFullNameArray = explode('.', $originalFileName);
                    $fileExtension = array_pop($fileFullNameArray);
                    $fileName = str_replace('.', '', microtime(true)) . '.' . $fileExtension;
                    $uploadedFile = PUBLIC_PATH.'/static/default/upload/video/' . $fileName;

                    $input = fopen("php://input", "r");
                    $temp = tmpfile();
                    $realSize = stream_copy_to_stream($input, $temp);
                    fclose($input);

                    $target = fopen($uploadedFile, "w");        
                    fseek($temp, 0, SEEK_SET);
                    stream_copy_to_stream($temp, $target);
                    fclose($target);

                    $fileSize = $realSize;
                } elseif(isset($_FILES['qqfile']['name'])) {
                    $originalFileName = $_FILES['qqfile']['name']; // from Opera - comes as simple form element input with type 'file'

                    $fileFullNameArray = explode('.', $originalFileName);
                    $fileExtension = array_pop($fileFullNameArray);
                    $fileName = str_replace('.', '', microtime(true)) . '.' . $fileExtension;
                    $uploadedFile = PUBLIC_PATH.'/static/default/upload/video/' . $fileName;

                    if( !move_uploaded_file($_FILES['qqfile']['tmp_name'], $uploadedFile) )
                            throw new Exception('Неудалось поместить video в нужную папку');
                    $fileSize = $_FILES['qqfile']['size'];
                }
                echo json_encode(array(
                    'path' => $fileName
                ));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
    }

}