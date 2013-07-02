<?php

class Application_Model_Kernel_Page_ContentPage extends Application_Model_Kernel_Page {

    private $_idContentPage;

    public function __construct($idContentPage, $idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, $position) {
        parent::__construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, self::TYPE_PAGE, $position);
        $this->_idContentPage = $idContentPage;
    }

    /**
     * get contentPage id
     * @access public 
     * @return int
     */
    public function getId() {
        return $this->_idContentPage;
    }

    /**
     * get contentPage name
     * @access public 
     * @return string
     */
    public function getName() {
        return $this->getContent()->getContentName();
    }

    /**
     * save contentPage data to db
     * @access public
     * @see Application_Model_Kernel_Page::save()
     * @return Application_Model_Kernel_ContentPage
     */
    public function save() {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        $insert = is_null($this->_idPage);
        try {
            $db->beginTransaction();
            $insert = is_null($this->_idPage);
            $this->savePageData(); //сохраняем даные страницы
            if ($insert) {
                $db->insert('contentPages', array(
                    'idPage' => $this->_idPage
                ));
                $this->_idContentPage = $db->lastInsertId();
            } else {
                //@зачем если у нас одно поле с одного id
            }
            $db->commit();
            //$this->clearCache();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';ContentPage.php');
            throw new Exception($e->getMessage());
        }
        return $this;
    }

    private function clearCache() {
        if (!is_null($this->getId())) {
            $cachemanager = Zend_Registry::get('cachemanager');
            $cache = $cachemanager->getCache('contentPage');
            if (!is_null($cache)) {
                $cache->remove($this->getId());
            }
        }
    }

    public function getCities() {
        return Application_Model_Kernel_City::getList($this->getId(), false, false, true, true, false, false, false, false, false, false, false)->data;
    }

    public function validate() {
        $e = new Application_Model_Kernel_Exception();
        $this->getRoute()->validate($e);
        $this->validatePageData($e);
        if ((bool) $e->current())
            throw $e;
    }

    public static function getSelf(stdClass &$data) {
        return new self($data->idContentPage, $data->idPage, $data->idRoute, $data->idContentPack, $data->pageEditDate, $data->pageStatus, $data->position);
    }

    /**
     * @param int $idContentPage
     * @throws Exception
     * @return Application_Model_Kernel_ContentPage
     */
    public static function getById($idContentPage) {
        $idContentPage = intval($idContentPage);
        //$cachemanager = Zend_Registry::get('cachemanager');
        //$cache = $cachemanager->getCache('contentPage');
        //if (($contentPage = $cache->load($idContentPage)) !== false) {
        //	return $contentPage;
        //} else {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('contentPages');
        $select->join('pages', 'contentPages.idPage = pages.idPage');
        $select->where('contentPages.idContentPage = ?', $idContentPage);
        $select->limit(1);
        if (($contentPageData = $db->fetchRow($select)) !== false) {
            $contentPage = self::getSelf($contentPageData);
            //$cache->save($contentPage);
            return $contentPage;
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
        //}
    }

    public static function getByPageId($idPage) {
        $idPage = (int) ($idPage);
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('contentPages');
        $select->join('pages', 'contentPages.idPage = pages.idPage');
        $select->where('contentPages.idPage = ?', $idPage);
        $select->limit(1);
        if (($contentPageData = $db->fetchRow($select)) !== false) {
            return self::getSelf($contentPageData);
        } else
            throw new Exception(self::ERROR_INVALID_ID);
    }

    public static function getList($order, $orderType, $content, $route, $search, $status, $page, $onPage, $limit) {
        $return = new stdClass();
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('contentPages');
        $select->join('pages', 'pages.idPage = contentPages.idPage');
        if ($route) {
            $select->join('routing', 'pages.idRoute = routing.idRoute');
        }
        if ($content) {
            $select->join('content', 'content.idContentPack = pages.idContentPack');
            $select->where('content.idLanguage = ?', Kernel_Language::getCurrent()->getId());
            if ($search) {
                $select->where('content.contentName = ?', $search);
            }
        }
        $select->where('pages.pageType = ?', self::TYPE_PAGE);
        if ($order)
            $select->order($order . ' ' . $orderType);
        else
            $select->order('pages.idPage DESC');
        if ($status !== false)
            $select->where('pages.pageStatus = ?', $status);
        if ($limit !== false && $page === false)
            $select->limit($limit);
        if ($page !== false) {
            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($onPage);
            $paginator->setPageRange(40);
            $paginator->setCurrentPageNumber($page);
            $return->paginator = $paginator;
        } else {
            $return->paginator = $db->fetchAll($select);
        }
        $return->data = array();
        $i = 0;
        foreach ($return->paginator as $contentPage) {
            $return->data[$i] = self::getSelf($contentPage);
            if ($route) {
                $url = new Application_Model_Kernel_Routing_Url($contentPage->url);
                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams($contentPage->defaultParams);
                $route = new Application_Model_Kernel_Routing($contentPage->idRoute, $contentPage->type, $contentPage->name, $contentPage->module, $contentPage->controller, $contentPage->action, $url, $defaultParams, $contentPage->routeStatus);
                $return->data[$i]->setRoute($route);
            }
            if ($content) {
                $contentLang = new Application_Model_Kernel_Content_Language($contentPage->idContent, $contentPage->idLanguage, $contentPage->idContentPack);
                $contentLang->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($contentPage->idContent));
                $return->data[$i]->setContent($contentLang);
            }
            $i++;
        }
        return $return;
    }

    public function show() {
        if ($this->getStatus() !== self::STATUS_SYSTEM) {
            $db = Zend_Registry::get('db');
            $this->_pageStatus = self::STATUS_SHOW;
            $this->savePageData();
            $this->clearCache();
        }
    }

    public function hide() {
        if ($this->getStatus() !== self::STATUS_SYSTEM) {
            $db = Zend_Registry::get('db');
            $this->_pageStatus = self::STATUS_HIDE;
            $this->savePageData();
            $this->clearCache();
        }
    }

    public function delete() {
        if ($this->getStatus() !== self::STATUS_SYSTEM) {
            $db = Zend_Registry::get('db');
            $db->delete('contentPages', "contentPages.idPage = {$this->_idPage}");
            $this->deletePage();
            $this->clearCache();
        }
    }

}