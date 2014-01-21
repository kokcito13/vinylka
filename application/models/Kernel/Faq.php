<?php

class Application_Model_Kernel_Faq extends Application_Model_Kernel_Page
{

    private $id;
    private $idPhoto1;
    private $photo1 = null;

    const ITEM_ON_PAGE = 16;

    private $statuses = array (
        0 => 'Предварительный заказ',
        1 => 'На складе',
        2 => 'В наличии',
        3 => 'Снята с производства'
    );

    public function __construct(
        $id, $idPhoto1, $idPage,
        $idRoute, $idContentPack, $pageEditDate,
        $pageStatus, $position
    )
    {
        parent::__construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, self::TYPE_FAQ, $position);
        $this->id       = $id;
        $this->idPhoto1 = $idPhoto1;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIdPhoto1()
    {
        return $this->idPhoto1;
    }

    public function getPhoto1()
    {
        if (is_null($this->photo1))
            $this->photo1 = Application_Model_Kernel_Photo::getById($this->idPhoto1);

        return $this->photo1;
    }

    public function setPhoto1(Application_Model_Kernel_Photo &$photo1)
    {
        $this->photo1 = $photo1;

        return $this;
    }

    public function setIdPhoto1($idPhoto1)
    {
        $this->idPhoto1 = $idPhoto1;
    }

    public function save()
    {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->beginTransaction();
            $insert = is_null($this->_idPage);
            $this->savePageData();
            $data = array (
                'id'       => $this->getId(),
                'idPhoto1' => $this->idPhoto1,
                'idPage' => $this->_idPage
            );
            if ($insert) {
                $db->insert('faq', $data);
                $this->id = $db->lastInsertId();
            } else {
                $db->update('faq', $data, 'id = ' . intval($this->id));
            }
            $db->commit();
//            $this->clearCache();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';product.php');
            throw new Exception($e->getMessage());
        }
    }

    private function clearCache()
    {
        if (!is_null($this->getidProject())) {
            $cachemanager = Zend_Registry::get('cachemanager');
            $cache        = $cachemanager->getCache('product');
            if (!is_null($cache)) {
                $cache->remove($this->getidProduct());
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
        return new self($data->id, $data->idPhoto1,
                        $data->idPage, $data->idRoute, $data->idContentPack,
                        $data->pageEditDate, $data->pageStatus, $data->position
        );
    }

    public static function loadCache($id)
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache        = $cachemanager->getCache('project');

        return $cache->load($id);
    }

    public static function getById($id)
    {
//		$cachemanager = Zend_Registry::get('cachemanager');
//		$cache = $cachemanager->getCache('department');
//		if (($project = $cache->load($idProject)) !== false) {
//			return $project;
//		} else {
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('faq');
        $select->join('pages', 'faq.idPage = pages.idPage');
        $select->where('id = ?', (int)$id);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
//				$project->completelyCache();
            return self::getSelf($productData);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
//		}
    }

    public static function getByIdPage($idPage)
    {
        $idPage = intval($idPage);

        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('faq');
        $select->join('pages', 'faq.idPage = pages.idPage');
        $select->where('pages.idPage = ?', $idPage);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
            return self::getSelf($productData);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
    }

    public function completelyCache()
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache        = $cachemanager->getCache('product');
        $cache->load($this->getIdPage());
        $this->getidPhoto1();
        $this->getRoute();
        $this->getContent();
        $cache->save($this);
    }

    public static function getList($order, $orderType, $content, $route, $searchName, $status, $page, $onPage, $limit, $group = true, $wher = false, $nextorder = false)
    {
        $return = new stdClass();
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('faq');
        $select->join('pages', 'pages.idPage = faq.idPage');
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
        $select->where('pages.pageType = ?', self::TYPE_FAQ);
        if ($wher) {
            $select->where($wher);
        }
        if ($order && $orderType) {
            if ($order == 'BY' && $orderType == 'RAND') {
                $select->order(new Zend_Db_Expr('RAND()'));
            } else {
                $select->order($order . ' ' . $orderType);
            }
        } else {
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
            $select->group('faq.id');
        if ($limit !== false)
            $select->limit($limit);
        if ($page !== false) {
            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($onPage);
            $paginator->setPageRange(5);
            $paginator->setCurrentPageNumber($page);
            $return->paginator = $paginator;
        } else {
            $return->paginator = $db->fetchAll($select);
        }
        $return->data = array ();
        $i            = 0;
        foreach ($return->paginator as $projectData) {
            $return->data[$i] = self::getSelf($projectData);
            if ($route) {
                $url           = new Application_Model_Kernel_Routing_Url($projectData->url);
                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams($projectData->defaultParams);
                $route         = new Application_Model_Kernel_Routing($projectData->idRoute, $projectData->type, $projectData->name, $projectData->module, $projectData->controller, $projectData->action, $url, $defaultParams, $projectData->routeStatus);
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
        $this->_pageStatus = self::STATUS_SHOW;
        $this->savePageData();
    }

    public function hide()
    {
        $this->_pageStatus = self::STATUS_HIDE;
        $this->savePageData();
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('faq', "faq.idPage = {$this->_idPage}");
        $this->deletePage();
    }
}

