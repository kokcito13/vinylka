<?php

abstract class Application_Model_Kernel_Page implements Application_Model_Kernel_Interface_Sort
{
    protected $_idPage;
    protected $_idRoute;
    protected $_idContentPack;
    protected $_pageEditDate;
    protected $_pageStatus;
    protected $_pageType;
    protected $_position;

    /**
     * @var Application_Model_Kernel_Routing
     */
    protected $_route = NULL;

    /**
     * @var Application_Model_Kernel_Content_Manager
     */
    protected $_contentManager = NULL;

    /**
     * @var Application_Model_Kernel_Content_Language
     */
    protected $_content = NULL;

    protected $_db;

    const STATUS_SHOW = 1;
    const STATUS_HIDE = 0;
    const STATUS_DEL = -1;
    const STATUS_SYSTEM = 2;
    const STATUS_NEW = 3;

    const TYPE_PAGE = 1;
    const TYPE_PROJECT = 2;
    const TYPE_FAQ = 4;

    const ERROR_INVALID_ID = 'INVALID ID GIVEN';
    const ERROR_INVALID_PAGE_ID = 'INVALID PAGE ID GIVEN';
    const ERROR_CONTENT_LANG_GIVEN = 'Wrong content lang given';
    const ERROR_CONTENT_MANAGER_GIVEN = 'Wrong content manager given';
    const ERROR_CONTENT_MANAGER_IS_NOT_DEFINED = 'Content manager is not defined';
    const ERROR_CONTENT_LANG_IS_NOT_DEFINED = 'Content lang model is not defined';

    const RELATION_TYPE_TOUR_TO_COUNTRY = 1;
    const RELATION_TYPE_TOUR_TO_CITY = 2;
    const RELATION_TYPE_TOUR_TO_ATTRACTION = 3;
    const RELATION_TYPE_PAGE_TO_ARTICLE = 4;

    public function __construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, $pageType, $position)
    {
        $this->_idPage = $idPage;
        $this->_idRoute = $idRoute;
        $this->_idContentPack = $idContentPack;
        $this->_pageEditDate = $pageEditDate;
        $this->_pageStatus = $pageStatus;
        $this->_pageType = $pageType;
        $this->_position = $position;

        $this->_db = Zend_Registry::get('db');
    }

    /**
     * @param Application_Model_Kernel_Content_Manager $contentManager
     * @throws Exception ERROR_CONTENT_MANAGER_GIVEN
     * @return $this
     */
    public function setContentManager(Application_Model_Kernel_Content_Manager $contentManager)
    {
        $this->_contentManager = $contentManager;

        return $this;
    }

    /**
     * @return Application_Model_Kernel_Content_Manager
     */
    public function getContentManager()
    {
        if (is_null($this->_contentManager)) {
            $this->setContentManager(Application_Model_Kernel_Content_Manager::getById($this->_idContentPack));
        }

        return $this->_contentManager;
    }

    /**
     * @return Application_Model_Kernel_Content_Language
     */
    public function getContent()
    {
        if (is_null($this->_content)) {
            $this->setContent(Application_Model_Kernel_Content_Language::get($this->_idContentPack, Kernel_Language::getCurrent()->getId()));
        }

        return $this->_content;
    }

    /**
     * @param Application_Model_Kernel_Content_Language $contentLang
     * @return $this
     */
    public function setContent(Application_Model_Kernel_Content_Language $contentLang)
    {
        $this->_content = $contentLang;

        return $this;
    }

    /**
     *
     * @param Application_Model_Kernel_Routing $route
     * @return $this
     */
    public function setRoute(Application_Model_Kernel_Routing $route)
    {
        $this->_route = $route;
        $this->_idRoute = $route->getId();

        return $this;
    }

    public function getIdRoute()
    {
        return $this->_idRoute;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setIdContentPack($id)
    {
        $this->_idContentPack = intval($id);

        return $this;
    }

    public function getIdContentPack()
    {
        return $this->_idContentPack;
    }

    /**
     * @param $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->_position = intval($position);

        return $this;
    }

    public function getPosition()
    {
        return $this->_position;
    }

    /**
     * @return Application_Model_Kernel_Routing
     */
    public function getRoute()
    {
        if (is_null($this->_route)) {
            $this->setRoute(Application_Model_Kernel_Routing::getById($this->_idRoute));
        }
        return $this->_route;
    }

    public function getIdPage()
    {
        return $this->_idPage;
    }

    public function getPageEditDate()
    {
        return $this->_pageEditDate;
    }

    public function getType()
    {
        return $this->_pageType;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->_pageStatus = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->_pageStatus;
    }

    public function setIdRoute($idRoute)
    {
        $this->_idRoute = intval($idRoute);

        return $this;
    }

    public function validatePageData(Application_Model_Kernel_Exception $e)
    {
        if (is_null($this->_contentManager)) {
            throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
        }
        $this->getContentManager()->validate($e);
    }

    public static function getRowById($idPage)
    {
        $db = Zend_Registry::get('db');
        $idPage = (int)$idPage;
        $select = $db->select()->from('pages');
        $select->where('pages.idPage = ?', $idPage);
        $select->limit(1);
        return $db->fetchRow($select);
    }

    /**
     * Save page data
     * @access protected
     * @return void
     */
    protected function savePageData()
    {
        $data = array(
            'idPage' => $this->_idPage,
            'idRoute' => $this->_idRoute,
            'pageEditDate' => $this->_pageEditDate,
            'pageStatus' => $this->_pageStatus,
            'pageType' => $this->_pageType,
            'position' => $this->_position,
            'idContentPack' => $this->_idContentPack,
        );
        $db = $this->_db;
        $this->getRoute()->save(); //save Route, get AI for Route
        $this->getContentManager()->saveContentData(); //save current content by content manager
        if (is_null($this->_idPage)) {
            $this->increasePosition();
            $this->setIdRoute($this->getRoute()->getId()); //set last AI route
            $this->setIdContentPack($this->getContentManager()->getIdContentPack()); //set AI idContent
            $data['idRoute'] = $this->getIdRoute();
            $data['idContentPack'] = $this->getIdContentPack();
            $db->insert('pages', $data);
            $this->_idPage = $db->lastInsertId();
            $this->getRoute()->setName('public-page-' . $this->getIdPage());
            $this->getRoute()->defaultParams->idPage = $this->getIdPage();
            $this->getRoute()->save();
        } else {
            $db->update('pages', $data, 'idPage = ' . intval($this->_idPage));
        }
    }

    abstract public function validate();

    abstract public function save();

    abstract public function show();

    abstract public function hide();

    abstract public function delete();

    protected function deletePage()
    {
        $this->_db->delete('pages', "pages.idPage = {$this->_idPage}");
        $this->getRoute()->delete();
        $this->getContentManager()->delete();
    }

    public function increasePosition()
    {
        $this->_db->update('pages', array('position' => new Zend_Db_Expr('position + 1')), 'pageType = ' . $this->getType());
    }

    public static function search($text)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select();
        $select->from('content', array(
            '*',
            'relevenceScore' => $db->quoteInto('MATCH(contentName, content, preview) AGAINST(?)', $text)
        ));
        $select->join('pages', 'pages.idContentPack = content.idContentPack');
        $select->where('MATCH(contentName, content, preview) AGAINST(?)', $text);
        $select->order('relevenceScore DESC');
        $select->limit(1);
        if (($result = $db->fetchRow($select)) !== false) {
            return Application_Model_Kernel_Routing::getById($result->idRoute)->getUrl();
        }

        return false;
    }

    public static function changePosition($idPage, $position)
    {
        $db = Zend_Registry::get('db');
        $db->update('pages', array("position" => $position), 'idPage = ' . (int)$idPage);
    }

    public static function changeStatus($idPage, $status)
    {
        $db = Zend_Registry::get('db');
        $db->update('pages', array("pageStatus" => $status), 'idPage = ' . (int)$idPage);
    }
}