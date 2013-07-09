<?php

class Admin_ProductController extends Zend_Controller_Action
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
        $this->view->headTitle()->append('Список товар');
        $this->view->categories = Application_Model_Kernel_Category::getListParent();
    }

    public function indexAction()
    {
        $this->view->add = (object)array(
            'link' => $this->view->url(array(), 'admin-product-add'),
            'alt'  => 'Добавить товар',
            'text' => 'Добавить товар'
        );

        $this->view->page = (int)$this->_getParam('page');

        $this->view->breadcrumbs->add('Список товар', '');
        $this->view->headTitle()->append('Список товар');
        $this->view->projectList = Application_Model_Kernel_Product::getList(false, false, true, true, false, false, $this->view->page, 15, false, true, false);
    }

    public function addAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->idPage = null;
        $this->view->tinymce = true;
        $this->view->back = true;
        $this->view->category = Application_Model_Kernel_Cat::getList(false, false, true, true, false, false, 20, true, false);
        $this->view->edit = false;
        $this->view->idPhoto1 = 0;
        $this->view->product = new Application_Model_Kernel_Product(null, null, null, null, null, time(), Application_Model_Kernel_Page_ContentPage::STATUS_SHOW, 0);

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $url = new Application_Model_Kernel_Routing_Url("/" . $data->url);

                $this->view->idPhoto1 = (int)$data->idPhoto1;

                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams();
                $route = new Application_Model_Kernel_Routing(null, Application_Model_Kernel_Routing::TYPE_ROUTE, '~public', 'default', 'public', 'show', $url, $defaultParams, Application_Model_Kernel_Routing::STATUS_ACTIVE);

                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);

                $this->view->product = new Application_Model_Kernel_Product(null, $this->view->idPhoto1, null, null, null, time(), Application_Model_Kernel_Page_ContentPage::STATUS_SHOW, 0);

                $this->view->product->setContentManager($contentManager);
                $this->view->product->setRoute($route);
                $this->view->product->setPrice($data->price)
                    ->setIdAmazon($data->idAmazon)
                    ->setSameProducts($data->sameProducts)
                    ->setProductUrl($data->productUrl)
                    ->setProductStatus($data->productStatus);
                $this->view->product->validate($data);
                $this->view->product->save();

                $this->view->product->saveCategoryByIdProduct($data->category);
                $this->view->product->saveTypesByIdProduct($data->types);

                $this->_redirect($this->view->url(array('page' => 1), 'admin-product-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $this->view->idAmazon = $this->_getParam('idAmazon');

            if ($this->view->idAmazon) {
                $amazon = new Application_Model_Kernel_Amazon('AKIAJHSI2YWBWH3MXHPA', 'AaptU8j65gKkTyPlilLzrDoqSgZgcGRdRo9E0K+M', 'com', 'vinylka-20');
                $item = $amazon->responseGroup('Large')->lookup($this->view->idAmazon)->Items->Item;

                if (!isset($item->Request->Errors)) {
                    $itemImage = $item->LargeImage->URL;
                    $itemAttributes = $item->ItemAttributes;
                    $itemEditorialReviews = $item->EditorialReviews;
                    $itemSimilarProducts = $item->SimilarProducts;
                    $itemPrice = $item->Offers->Offer->OfferListing->Price;
                    $ids = array();
                    foreach ($itemSimilarProducts->SimilarProduct as $same) {
                        $ids[] = $same->ASIN;
                    }

                    $_POST['url'] = str_replace(' ', '_', strtolower($itemAttributes->Title));
                    $_POST['url'] = str_replace('"', '_', strtolower($_POST['url']));
                    $_POST['url'] = str_replace(',', '_', strtolower($_POST['url']));

                    $_POST['price'] = ceil(($itemPrice->Amount / 100) * 8);
                    $_POST['idAmazon'] = $this->view->idAmazon;
                    $_POST['sameProducts'] = join(', ', $ids);
                    $_POST['productUrl'] = $item->DetailPageURL;

                    $_POST['content'][1]['content'] = join('<br/>', $itemAttributes->Feature) . '<br/><br/>' . $itemEditorialReviews->EditorialReview->Content;
                    $_POST['content'][1]['contentName'] = $itemAttributes->Title;

                    $this->view->photo1 = new Application_Model_Kernel_Photo(null, null, '', '', 0);

                    $photo = $this->view->photo1->movePhotoToTmpDir(array('http_url' => $itemImage, 'name' => $_POST['url']));

                    $this->view->photo1->validate($itemImage);
                    $this->view->photo1->upload($photo['tmp'], $photo['name']);
                    $this->view->photo1->save();

                    $this->view->idPhoto1 = $this->view->photo1->getId();
                } else {
                    var_dump($item->Request->Errors);
                }
            }
        }
        $this->view->breadcrumbs->add('Добавить товар', '');
        $this->view->headTitle()->append('Добавить');
    }

    public function editAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->back = true;
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->tinymce = true;
        $this->view->edit = true;
        $this->view->product = Application_Model_Kernel_Product::getById((int)$this->_getParam('idProduct'));
        $this->view->category = Application_Model_Kernel_Cat::getList(false, false, true, true, false, false, 20, true, false);
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

                    $value = $getContent[$lang->getId()];
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
                    $fields = array();
                }

                if (count($data->content) > count($getContent)) {
                    foreach ($getContent as $key => $value) {
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
                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
                $this->view->product->setIdPhoto1($this->view->idPhoto1);

                $this->view->product->setPrice($data->price);
                $this->view->product->setIdAmazon($data->idAmazon);
                $this->view->product->setSameProducts($data->sameProducts);
                $this->view->product->setProductUrl($data->productUrl);
                $this->view->product->setProductStatus($data->productStatus);

                $this->view->product->deleteCatecorys();
                $this->view->product->deleteTypes();

                $this->view->product->validate($dataContent);
                $this->view->product->save();

                $this->view->product->saveCategoryByIdProduct($data->category);
                $this->view->product->saveTypesByIdProduct($data->types);

                $this->_redirect($this->view->url(array('page' => 1), 'admin-product-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
            $_POST['url'] = mb_substr(Application_Model_Kernel_Routing::getById($this->view->product->getIdRoute())->getUrl(), 1);
            $_POST['price'] = $this->view->product->getPrice();
            $_POST['idAmazon'] = $this->view->product->getIdAmazon();
            $_POST['sameProducts'] = $this->view->product->getsameProducts();
            $_POST['productUrl'] = $this->view->product->getProductUrl();

            $_POST['content'] = $this->view->product->getContentManager()->getContents();
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

            $this->view->project = Application_Model_Kernel_Project::getById((int)$data->idProject);
            if ($this->view->project->getProjectStatus() == 1)
                $this->view->project->setProjectStatus(0);
            else
                $this->view->project->setProjectStatus(1);
            $this->view->project->save();
            echo 1;
            exit();
        }
        echo 0;
        exit();
    }

    public function mainchangeAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();

            $this->view->project = Application_Model_Kernel_Project::getById((int)$data->idProject);
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

    public function changepositionprojectAction()
    {

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            Application_Model_Kernel_Project::changePosition((int)$data->id, (int)$data->val);
            echo 1;
        }
    }

    public function getBookInfo($isbn, $access_key, $secure_access_key)
    {
        // формируем список параметров запроса
        $fields = array();
        $fields['AWSAccessKeyId'] = $access_key;
        $fields['ItemId'] = $isbn;
        $fields['MerchantId'] = 'All';
        $fields['Operation'] = 'ItemLookup';
        $fields['ResponseGroup'] = 'Request,Large';
        $fields['Service'] = 'AWSECommerceService';
        $fields['Version'] = '2009-01-06';
        $fields['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');

        // сортируем параметры согласно спецификации Amazon API
        ksort($fields);

        $query = array();
        foreach ($fields as $key => $value) {
            $query[] = "$key=" . urlencode($value);
        }

        // подписываем запрос секретным ключом
        $string = "GET\nwebservices.amazon.com\n/onca/xml\n" . implode('&', $query);
        $signed = urlencode(base64_encode(hash_hmac('sha256', $string, $secure_access_key, true)));

        // формируем строку запроса к сервису
        $url = 'http://webservices.amazon.com/onca/xml?' . implode('&', $query) . '&Signature=' . $signed;

        $url = 'http://webservices.amazon.com/onca/xml?
        Service=AWSECommerceService&
        AWSAccessKeyId=' . $access_key . '&
        Operation=ItemLookup&
        ItemId=B00008OE6I
        &Timestamp=' . gmdate('Y-m-d\TH:i:s\Z') . '
        &Signature=' . $signed;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] != '200') return false;

        return $data;
    }
}