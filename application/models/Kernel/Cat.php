<?php

class Application_Model_Kernel_Cat extends Application_Model_Kernel_Page
{

    private $idCat;
    private $productIds = array();

    /**
     * @var Application_Model_Kernel_Gallery
     */
    public function __construct($idCat, $idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, $position)
    {
        parent::__construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, self::TYPE_PROJECT, $position);
        $this->idCat = $idCat;
    }

    public function getIdCat()
    {
        return $this->idCat;
    }

    public function save()
    {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->beginTransaction();
            $insert = is_null($this->_idPage);
            $this->savePageData(); //сохраняем даные страницы
            $data = array(
                'idPage' => $this->getIdPage(),
            );
            if ($insert) {
                $db->insert('category', $data);
                $this->idCat = $db->lastInsertId();
            }
            else {
                $db->update('category', $data, 'idCat = ' . intval($this->idCat));
            }
            $db->commit();
//            $this->clearCache();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';category.php');
            throw new Exception($e->getMessage());
        }
    }

    private function clearCache()
    {
        if (!is_null($this->getidProject())) {
            $cachemanager = Zend_Registry::get('cachemanager');
            $cache = $cachemanager->getCache('category');
            if (!is_null($cache)) {
                $cache->remove($this->getIdCat());
            }
        }
    }

    public function validate($data = false)
    {
        $e = new Application_Model_Kernel_Exception();
        $this->getRoute()->validate($e);
        $this->validatePageData($e);

        if ($data != false) {
            $data->url = trim($data->url);
            if (empty($data->url))
                throw new Exception(' Пустой URL ');
            $langs = Kernel_Language::getAll();
            foreach ($langs as $lang) {
                if (empty($data->content[$lang->getId()]['contentName']))
                    throw new Exception(' Пустой поле "Название" ' . $lang->getFullName());
            }
        }

        if ((bool)$e->current())
            throw $e;
    }

    private static function getSelf(stdClass &$data)
    {
        return new self($data->idCat,
                        $data->idPage, $data->idRoute, $data->idContentPack,
                        $data->pageEditDate, $data->pageStatus, $data->position);
    }

    public static function loadCache($id)
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('category');

        return $cache->load($id);
    }

    public static function getById($idCat)
    {
        $idCat = (int)$idCat;
//		$cachemanager = Zend_Registry::get('cachemanager');
//		$cache = $cachemanager->getCache('department');
//		if (($project = $cache->load($idProject)) !== false) {
//			return $project;
//		} else {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('category');
        $select->join('pages', 'category.idPage = pages.idPage');
        $select->where('idCat = ?', $idCat);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
//				$project->completelyCache();
            return self::getSelf($productData);
        }
        else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
//		}
    }

    public static function getByIdPage($idPage)
    {
        $idPage = intval($idPage);

        $db = Zend_Registry::get('db');
        $select = $db->select()->from('category');
        $select->join('pages', 'category.idPage = pages.idPage');
        $select->where('pages.idPage = ?', $idPage);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
            return self::getSelf($productData);
        }
        else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
    }

    public function completelyCache()
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('category');
        $cache->load($this->getIdPage());
        $this->getidPhoto1();
        $this->getIdGallery2();
        $this->getRoute();
        $this->getContent();
        $cache->save($this);
    }

    public static function getList($order, $orderType, $content, $route, $searchName, $status, $limit, $group = true, $wher = false, $nextorder = false)
    {
        $return = new stdClass();
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('category');
        $select->join('pages', 'pages.idPage = category.idPage');

        if ($route) {
            $select->join('routing', 'pages.idRoute = routing.idRoute');
        }
        if ($content) {
            $select->join('content', 'content.idContentPack = pages.idContentPack');
            $select->where('content.idLanguage = ?', Kernel_Language::getCurrent()->getId());
            if ($searchName) {
                $select->where('content.contentName = ?', $searchName);
            }
        }
        $select->where('pages.pageType = ?', self::TYPE_PROJECT);
        if ($wher) {
            $select->where($wher);
        }
        if ($order && $orderType) {
            if ($order == 'BY' && $orderType == 'RAND') {
                $select->order(new Zend_Db_Expr('RAND()'));
            }
            else {
                $select->order($order . ' ' . $orderType);
            }
        }
        else {
            if (!$nextorder) {
                $select->order('pages.idPage DESC');
            }
        }
        if ($nextorder) {
            $select->order($nextorder);
        }
        if ($status !== false)
            $select->where('pages.pageStatus = ?', $status);
        if ($group !== false)
            $select->group('category.idCat');
        if ($limit !== false)
            $select->limit($limit);

        $return->paginator = $db->fetchAll($select);
        $return->data = array();
        $i = 0;
        foreach ($return->paginator as $projectData) {
            $return->data[$i] = self::getSelf($projectData);
            if ($route) {
                $url = new Application_Model_Kernel_Routing_Url($projectData->url);
                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams($projectData->defaultParams);
                $route = new Application_Model_Kernel_Routing($projectData->idRoute, $projectData->type, $projectData->name, $projectData->module, $projectData->controller, $projectData->action, $url, $defaultParams, $projectData->routeStatus);
                $return->data[$i]->setRoute($route);
            }
            if ($content) {
                $contentLang = new Application_Model_Kernel_Content_Language($projectData->idContent, $projectData->idLanguage, $projectData->idContentPack);
                $contentLang->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($projectData->idContent));
                $return->data[$i]->setContent($contentLang);
            }
            $i++;
        }

        return $return;
    }

    public function show()
    {
        $db = Zend_Registry::get('db');
        $this->_pageStatus = self::STATUS_SHOW;
        $this->savePageData();
//        $this->clearCache();
    }

    public function hide()
    {
        $db = Zend_Registry::get('db');
        $this->_pageStatus = self::STATUS_HIDE;
        $this->savePageData();
//        $this->clearCache();
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('category', "category.idPage = {$this->_idPage}");
        $this->deletePage();
    }

    public static function changePosition($idPage, $position)
    {

        $db = Zend_Registry::get('db');
        $db->update('category', array("position" => $position), 'idPage = ' . (int)$idPage);

        return true;
    }

    public function getListIdProductByCategory(){
        if (empty($this->productIds)) {
            $db = Zend_Registry::get('db');
            $select = $db->select()->from('categorie_product');
            $select->where('categorie_product.idCategorie = ' . (int) $this->idCat);
            if (false !== ($result = $db->fetchAll($select))) {
                foreach ($result as $category) {
                    $this->productIds[] = $category->idProduct;
                }
            }
        }

        return $this->productIds;
    }
}

