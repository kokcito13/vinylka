<?php
class ErrorController extends Zend_Controller_Action
{
    private $ErrorCount = 0;

    public function errorAction()
    {
        if ($_SERVER['SERVER_NAME'] !== 'vinylka.com.ua') {
            $this->_helper->layout()->setLayout('error');
            $this->_helper->viewRenderer->setNoRender(true);
        }

        $errors = $this->_getParam('error_handler');
        $this->getResponse()->setHttpResponseCode(404);
        $this->view->message = 'Application error';
        if (($log = $this->getLog()) !== false)
            $log->crit($this->view->message, $errors->exception);
        $this->view->exception = $errors->exception;
        $this->view->request = $errors->request;
        $this->view->title = "404 ошибка - страница не найдена";
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('Log'))
            return false;
        $log = $bootstrap->getResource('Log');

        return $log;
    }

}
