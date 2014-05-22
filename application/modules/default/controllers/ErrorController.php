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
        if (isset($errors->exception)) {
            $var = "<h3>Exception information:</h3>
                <p><b>Message:</b> " . $errors->exception->getMessage() . "</p>

                <h3>Stack trace:</h3>
                <pre>" . $errors->exception->getTraceAsString() . "</pre>

                <h3>Request Parameters:</h3>
                <pre>" . var_export($errors->request->getParams(), true) . "</pre>

                <pre>URL: - http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."</pre>";

            $mail = new Zend_Mail('UTF-8');
            $mail->setBodyHtml($var);
            $mail->setFrom('manager@vinylka.com.ua', 'Ошибка на '.$_SERVER['SERVER_NAME']);
            $mail->addTo('oklosovich@gmail.com', 'Ошибка на '.$_SERVER['SERVER_NAME']);
            $mail->setSubject('Ошибка на '.$_SERVER['SERVER_NAME']);
            $mail->send();
        }
        $this->view->request = $errors->request;
        $this->view->title   = "404 ошибка - страница не найдена";
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
