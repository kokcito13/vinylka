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
    
    public function contactAction() {
               
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->contentPage = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage)->getContent()->getFields();
        
        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText(); 
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }
    
    public function guestbookAction() {
               
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->contentPage = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage)->getContent()->getFields();
        
        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText(); 
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }
    
    public function aboutAction() {
               
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->contentPage = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage)->getContent()->getFields();
        
        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText(); 
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }
    
    
}