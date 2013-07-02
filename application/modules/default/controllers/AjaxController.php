<?php

class AjaxController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }

    public function getAjaxListAction()
    {
        $result = array();
        $types = array();
        $where = false;
        if ($this->getRequest()->isGet()) {
            $data = (object)array_merge($this->getRequest()->getPost(), $_GET);
            $idCat = (int)$this->_getParam('idCat');

            if ($data->types) {
                $types = explode('-', $data->types);
                $productsIds = Application_Model_Kernel_Category::getProductsIdsByTypes($types);
                if (empty($productsIds)) {
                    $productsIds[] = 0;
                }
                $where = 'products.idProduct IN (' . join(',', $productsIds) . ')';
            }


            if ($idCat) {
                $this->view->page = Application_Model_Kernel_Cat::getById($idCat);
                $ids = $this->view->page->getListIdProductByCategory();
                if (!empty($productsIds)) {
                    $ids = array_intersect($ids, $productsIds);
                    if (empty($ids)) {
                        $ids[] = 0;
                    }
                }
                $where = 'products.idProduct IN (' . join(',', $ids) . ')';
            }
            
            $this->view->publicList = Application_Model_Kernel_Product::getList(false, false, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, $data->page, Application_Model_Kernel_Product::ITEM_ON_PAGE, Application_Model_Kernel_Product::ITEM_ON_PAGE, true, $where);

            $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
            $view->publicList = $this->view->publicList;

            $result['html'] = $view->render('block/page_items.phtml');
//            $result['data'] = array_merge($this->getRequest()->getPost(), $_GET)+array('cat'=>$idCat);
            $result['success'] = true;
        }
        else {
            $result['error'] = 'not good method';
        }

        echo json_encode((object)$result);
        exit;
    }

    public function indexAction()
    {
        $idPage = (int)$this->_getParam('idPage');
        session_start();
        if ($this->getRequest()->isPost() || true) {
            $data = (object)array_merge($this->getRequest()->getPost(), $_GET);
            switch ($data->type) {
                case 'CHECK_CAPTCHA':
                    if ($_SESSION['captcha'] == $data->result) {
                        echo true;
                    }
                    else {
                        echo false;
                    }
                    break;
                case 'VALIDATE_EMAIL':
                    mail('oklosovich@gmail.com', 'pizza send comment', 'want send the New comment on pizzza<br/>' . $data->email);
                    list($user, $url) = explode("@", $data->email);
                    //if($this->check_domain_availible($url)) {
                    echo true;
                    //} else {
                    //	echo false;
                    //}
                    break;
                case 'ADD_COMMENT':
                    $addComment = new Application_Model_Kernel_Comment(null, $idPage, 0, mysql_escape_string($data->name), mysql_escape_string($data->mail), mysql_escape_string($data->text), time(), ip2long($_SERVER['REMOTE_ADDR']));
                    $addComment->save();
                    $data->date = date('d.m.Y H:i', $addComment->getCommentDate());
                    $a = rand(0, 9);
                    $b = rand(0, 9);
                    $_SESSION['captcha'] = $a + $b;
                    $data->recaptcha = $a . "+" . $b . "=";
                    $data->rand = rand(0, 1000);
                    $text = $data->name . '<br/>' . $data->mail . '<br/>' . $data->text . '<br/>' . date('d.m.Y', time());
                    if ($data->url)
                        $text .= '<br/>' . $data->url;
                    $mail = new Zend_Mail('UTF-8');
                    $mail->setBodyHtml($text);
                    $mail->setFrom('oklosovich@i.ua', 'Коммент с пиццерий');
                    $mail->addTo('oklosovich@gmail.com', 'Коммент с пиццерий');
                    $mail->setSubject('Коммент с пиццерий Pizzza.com.Ua');
                    $mail->send();
                    echo json_encode($data);
                    break;
            }
        }
    }

    public function addOrderAction()
    {
        $idPage = (int)$this->_getParam('idPage');

        if ($this->getRequest()->isPost() || true) { echo 111;
//            $data = (object)array_merge($this->getRequest()->getPost(), $_GET);
//
//            $addComment = new Application_Model_Kernel_Comment(null, $data->id, $data->parent, $data->name, $data->email, $data->message, time(), ip2long($_SERVER['REMOTE_ADDR']), $data->type);
//            $addComment->save();
//            $return = array();
//            $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
//            if ($data->parent == 0) {
//                $view->comment = $addComment;
//                $view->type = $data->type;
//                $view->id = $data->id;
//                $return['html'] = $view->render('block/comment_item_first.phtml');
//            }
//            else {
//                $view->parentComments = array();
//                $view->parentComments[] = $addComment;
//                $return['html'] = $view->render('block/comment_item.phtml');
//            }
//            $return['data'] = $data;
//            echo json_encode($return);
        }
    }



}
