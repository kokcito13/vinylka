<?php
class FaqController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->menu = 'faq';
    }

    public function indexAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->page = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage);
        $this->view->contentPage = $this->view->page->getContent()->getFields();

        $this->view->faqs = Application_Model_Kernel_Faq::getList(false, false, true, true, false, false, 1, 15, false, true, false);

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }

    public function showAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->page = Application_Model_Kernel_Faq::getByIdPage($this->view->idPage);
        $this->view->contentPage = $this->view->page->getContent()->getFields();

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }
}