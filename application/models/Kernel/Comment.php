<?php

class Application_Model_Kernel_Comment {

    private $idComment;
    private $idOwner;
    private $parentIdComment;
    private $commentNick;
    private $commentEmail;
    private $commentText;
    private $commentDate;
    private $commentIp;
    private $commentType = 1;
    private $commentStatus = 0;

    const TYPE_PUBLIC = 1;
    const TYPE_PAGE = 2;

    const STATUS_CREATE = 0;
    const STATUS_SHOW = 1;
    
    public function __construct($idComment, $idOwner, $parentIdComment, $commentNick, $commentEmail, $commentText, $commentDate, $commentIp, $commentType = self::TYPE_PUBLIC, $commentStatus = self::STATUS_CREATE) {
        $this->idComment = $idComment;
        $this->idOwner = $idOwner;
        $this->parentIdComment = $parentIdComment;
        $this->commentNick = $commentNick;
        $this->commentEmail = $commentEmail;
        $this->commentText = $commentText;
        $this->commentDate = $commentDate;
        $this->commentIp = $commentIp;
        $this->commentType = $commentType;
        $this->commentStatus = $commentStatus;
    }

    public function getIdComment() {
        return $this->idComment;
    }
    
    public function getCommentStatus() {
        return $this->commentStatus;
    }
    public function readComment(){
        $this->commentStatus = self::STATUS_SHOW;
    }

    public function getCommentNick() {
        return $this->commentNick;
    }

    public function getCommentText() {
        return $this->commentText;
    }
    
    public function getCommentEmail() {
        return $this->commentEmail;
    }

    public function getCommentDate() {
        return $this->commentDate;
    }
    
    public function getParentIdComment() {
        return $this->parentIdComment;
    }

    public static function getSelf($data) {
        return new self($data->idComment, $data->idOwner, $data->parentIdComment,
                        $data->commentNick, $data->commentEmail, $data->commentText,
                        $data->commentDate, $data->commentIp, $data->commentType,
                        $data->commentStatus );
    }

    public static function getList($limit = false, $where = false) {
        $db = Zend_Registry::get('db');
        $return = array();
        $select = $db->select()->from('comments');
        if( $where !== false ){
            $select->where( $where );
        }
        $select->order('comments.idComment ASC');
        if ($limit !== false)
            $select->limit($limit);
        $i = 0;
        $results = $db->fetchAll($select);
        foreach ( $results as $result ) {
            $return[$result->idComment] = self::getSelf($result);
            $i++;
        }
        return $return;
    }

    public function save() {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->beginTransaction();
            $data = array(
                'idOwner' => $this->idOwner,
                'parentIdComment' => $this->parentIdComment,
                'commentNick' => $this->commentNick,
                'commentEmail' => $this->commentEmail,
                'commentText' => $this->commentText,
                'commentDate' => $this->commentDate,
                'commentIp' => $this->commentIp,
                'commentType' => $this->commentType,
                'commentStatus'=>$this->commentStatus
            );
            if (is_null($this->idComment)) {
                $db->insert('comments', $data);
                $this->idComment = $db->lastInsertId();
            } else {
                $db->update('comments', $data, 'idComment = ' . (int) $this->idComment);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';comments.php');
            throw new Exception($e->getMessage());
        }
    }

    public static function getById($idComment) {
        $idComment = (int) $idComment;
        $db = Zend_Registry::get('db');
        $return = array();
        $select = $db->select()->from('comments');
        $select->where('comments.idComment = ?', $idComment);
        $select->limit(1);
        $result = $db->fetchRow($select);
        return self::getSelf($result);
    }

    public static function getByParentIdComment($parentIdComment) {
        $parentIdComment = (int) $parentIdComment;
        $db = Zend_Registry::get('db');
        $return = array();
        $select = $db->select()->from('comments');
        $select->where('comments.parentIdComment = ?', $parentIdComment);
        $returnGeter = $db->fetchAll($select);
        $i=0;
        foreach ( $returnGeter as $result ) {
            $return[$i] = self::getSelf($result);
            $i++;
        }
        return $return;
    }

    public static function getByidOwner($idOwner) {
        $idOwner = (int) $idOwner;
        $db = Zend_Registry::get('db');
        $return = array();
        $select = $db->select()->from('comments');
        $select->where('comments.idOwner = ?', $idOwner);
        $select->order('idComment');
        foreach ($db->fetchAll($select) as $result) {
            $return[] = self::getSelf($result);
        }
        return $return;
    }

    public function delete() {
		$db = Zend_Registry::get('db');
		$db->delete('comments',"comments.idComment = {$this->idComment}");
	}
    
}