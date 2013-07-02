<?php
class Admin_GalleryController extends Zend_Controller_Action {
	
	public function preDispatch() {
		if(!Application_Model_Admin_Admin::isAuthorized())
			$this->_redirect($this->view->url(array(),'admin-login'));
		else
			$this->view->blocks = (object)array('menu' => true);
		$this->view->back = false;
		$this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
		$this->view->headTitle()->append('Галерея');
		$this->view->header = false;
	}
	
	public function indexAction() {
		
	}
	
	public function addAction() {
		
	}
	
	public function editAction() {
		
	}

	public function photosAction() {
		$this->view->back = true;
		$this->view->headTitle()->append('Фотографии');
		$fromType = intval($this->_getParam('fromType'));
		if ($fromType !== 0)
			$this->view->breadcrumbs->clear();
		switch ($fromType) {
			/*case Application_Model_Kernel_Page::TYPE_RESTAURANT:
				$this->view->breadcrumbs->add('Список всех городов',$this->view->url(array(
					'page' => $this->view->page,
					'idCountry' => 0
				),'admin-city-index'));
			break;
			case Application_Model_Kernel_Page::TYPE_RESTAURANT:
				$this->view->breadcrumbs->add('Список всех стран',$this->view->url(array('page' => $this->view->page),'admin-country-index'));
			break;
			case Application_Model_Kernel_Page::TYPE_ATTRACTION:
				$this->view->breadcrumbs->add('Список всех обьектов',$this->view->url(array(
					'page' => $this->view->page,
					'idCountry' => 0,
					'idCity' => 0
				),'admin-attraction-index'));
			break;
			*/
			case Application_Model_Kernel_Gallery::HEADER:
				$this->view->header = true;
				$this->view->breadcrumbs->clear();
				$this->view->breadcrumbs->add('Фотографии в шапку', '#');
			break;
		}
		$this->view->breadcrumbs->add('Добавление фотографий', '');	
		$this->view->idGallery = intval($this->_getParam('idGallery'));
		$this->view->galleryPhotos =  Application_Model_Kernel_Gallery::getGalleryPhotos($this->view->idGallery);
	}
	
	public function uploadAction() {
		$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->disableLayout();
   		if ($this->getRequest()->isPost() && isset($_FILES['photo'])) {
   			try {
   				$idPhoto = intval($this->_getParam('idPhoto'));
   				$idGallery = intval($this->_getParam('idGallery'));
   				if ($idPhoto === 0) 
					$photo = new Application_Model_Kernel_Photo(null, null, '', '', 0);
				else
					$photo = Application_Model_Kernel_Photo::getById($idPhoto);
				$photo->validate($_FILES['photo']['tmp_name']);
				$photo->upload($_FILES['photo']['tmp_name'],  $_FILES['photo']['name']);
				$photo->save();
				$gallery = Application_Model_Kernel_Gallery::getById($idGallery);
				$gallery->addPhotoToGallery($photo);
				echo json_encode(array(
					'idPhoto' => $photo->getId(),
					'path' => $photo->getPath( Application_Model_Kernel_Photo::PREV_GALLERY )
				));
   			} catch(Application_Model_Kernel_Exception $e) {
				$this->view->ShowMessage($e->getMessages());
			} catch(Exception $e) {
				$this->view->ShowMessage($e->getMessage());
			}
   		}
	}
	
	public function statusAction() {
	   	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->disableLayout();
   		if($this->getRequest()->isPost()) {
			$data = (object)$this->getRequest()->getPost();
			switch ($data->type) {
				case 2: //DELETE
					$photo = Application_Model_Kernel_Photo::getById(intval($data->photoId));
					$photo->delete();
				break;
				case 4: //SORT
					$gallery = Application_Model_Kernel_Gallery::getById(intval($data->galleryId));
					$gallery->sortPhotos($data->sortData);
				break;
			}
   		}
	}
	
}