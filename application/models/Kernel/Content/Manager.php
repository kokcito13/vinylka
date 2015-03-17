<?php

/**
 * Application_Model_Kernel_Content
 *
 * Content Manager of all langs
 *
 * @author <vlad.melanitski@gmail.com>
 * @package Content
 * @version 1.0
 */
class Application_Model_Kernel_Content_Manager
{

    private $_idContentPack = null;

    /**
     * @var Application_Model_Kernel_Content_Language[]
     */
    private $_content;

    const ERROR_EMPTY_CONTENT_ARRAY_GIVEN = 'An empty array of content given';
    const ERROR_NOT_CONTENT_LANG_GIVEN = 'Not content lang array given';
    const ERROR_CONTENT_LANG_PACK_GIVEN = 'Given content lang have different Content Manager';


    public function __construct($idContentPack, array $contentLangs)
    {
        if (empty($contentLangs)) {
            $langs = Kernel_Language::getAll();
            foreach ($langs as $lang) {
                $this->_content[$lang->getId()] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
            }
        } else {
            foreach ($contentLangs as $contentLang) {
                if (get_class($contentLang) === 'Application_Model_Kernel_Content_Language') {
                    if ($idContentPack !== $contentLang->getIdContentPack())
                        throw new Exception(self::ERROR_CONTENT_LANG_PACK_GIVEN);
                    $this->_content[$contentLang->getIdLang()] = $contentLang;
                } else {
                    throw new Exception(self::ERROR_NOT_CONTENT_LANG_GIVEN);
                }
            }
        }
        $this->_idContentPack = $idContentPack;
    }

    public function addContentLanguage($idLanguage, Application_Model_Kernel_Content_Language $content)
    {
        $this->_content[$idLanguage] = $content;
        if (!is_null($content->getFields())) {
            $this->_content[$idLanguage]->setFields('', '');
        }
    }

    public function setContentLanguage()
    {

    }

    /**
     * Save all content langs
     * @access public
     * @return Application_Model_Kernel_Content_Manager
     */
    public function saveContentData()
    {
        if (is_null($this->_idContentPack)) {
            $db = Zend_Registry::get('db');
            $db->insert('contentPacks', array());
            $this->_idContentPack = $db->lastInsertId();
            $this->setIdContentPack();
        }
        foreach ($this->_content as $contentLang) {
            $contentLang->save();
        }
        return $this;
    }

    public function getIdContentPack()
    {
        return $this->_idContentPack;
    }

    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @name setIdContentPack
     * @return void
     */
    protected function setIdContentPack()
    {
        foreach ($this->_content as $contentLang) {
            $contentLang->setidContentPack($this->_idContentPack);
        }
    }

    public function setLangContent($langId, $fieldsArray)
    {
        if (isset($this->_content[$langId])) {
            $this->_content[$langId]->setFieldsArray($fieldsArray);
        } else
            throw new Exception(Kernel_Language::ERROR_INVALID_LANG_ISO_NAME);
    }

    /**
     * @param $isoId
     * @return array
     * @throws Exception
     */
    public function getLangContent($isoId)
    {
        if (isset($this->_content[$isoId])) {
            return Application_Model_Kernel_Content_Fields::getFieldsByIdContent($this->_content[$isoId]->getId());
        } else
            throw new Exception(Kernel_Language::ERROR_INVALID_LANG_ISO_NAME);
    }

    /**
     * @access public
     * @name getLangs
     * @return array[][]
     */
    public function getContents()
    {
        $data = array();
        foreach ($this->_content as $content) {
            $data[$content->getIdLang()] = $this->getLangContent($content->getIdLang());
        }
        return $data;
    }

    /**
     * @param $idContentPack
     * @return Application_Model_Kernel_Content_Manager
     */
    public static function getById($idContentPack)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('content');
        $select->where('content.idContentPack = ?', intval($idContentPack));
        $contentsData = $db->fetchAll($select);
        $contentList = array();
        foreach ($contentsData as $content) {
            $contentList[$content->idLanguage] = new Application_Model_Kernel_Content_Language($content->idContent, $content->idLanguage, $content->idContentPack);
            $contentList[$content->idLanguage]->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($content->idContent));
        }
        return new self($idContentPack, $contentList);
    }

    public function getContentId()
    {
        return $this->_idContent;
    }

    public function delete()
    {
        foreach ($this->_content as $contentLang) {
            $contentLang->delete();
        }
        $db = Zend_Registry::get('db');
        $db->delete('contentPacks', 'idContentPack = ' . $this->_idContentPack);
    }

    public function validate(Application_Model_Kernel_Exception &$e)
    {
//        foreach ($this->_content as $contentLang)
//            $contentLang->validate($e);
    }

}
