<?php
/**
 * Плагин использования layout'a текущего модуля
 *
 * @name Kernel_Layouts
 * @author Vlad Melanitski <vlad.melanitski@gmail.com>
 * @version 1.0
 * @category Плагины
 * @package Общие
 */
class Kernel_Layouts extends Zend_Controller_Plugin_Abstract{
	
	/**
	* Установка пути к layout'у
	* 
	* @name dispatchLoopStartup
	* @param Zend_Controller_Request_Abstract $request
	* @return void
	*/
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request){
        $layout = Zend_Layout::getMvcInstance();   
        $front = Zend_Controller_Front::getInstance();
        $moduleName = $this->getRequest()->getModuleName();
        $layout->setLayoutPath(APPLICATION_PATH . '/modules/' . $moduleName . '/views/layouts');
    }
 
}
?>
