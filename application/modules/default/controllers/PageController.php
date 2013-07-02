<?php
class PageController extends Zend_Controller_Action {

    public function preDispatch() {

    }

    public function showAction() {
               
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->contentPage = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage)->getContent()->getFields();
        
        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText(); 
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }
    
    public function sitemapAction()
    {
        $contentPages = Application_Model_Kernel_Page_ContentPage::getList(false, false, false, true, false, 1, false, false, false)->data;
        $products = Application_Model_Kernel_Product::getList(false, false, false, true, false, 1, false, false, false)->data;
        $cats = Application_Model_Kernel_Cat::getList(false, false, false, true, false, 1, false)->data;

        $pages = array_merge($contentPages,$products,$cats);

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // create container
        $container = new Zend_Navigation();

        foreach ($pages as $page) {

            $container->addPage(Zend_Navigation_Page::factory(array(
                                                                   'uri' => $page->getRoute()->getUrl(),
                                                              )));

//            var_dump( get_class_methods( $router ) );
//            var_dump(  $router );
//
//            exit;
        }

        echo $this->view->navigation()->sitemap($container);
    }
}