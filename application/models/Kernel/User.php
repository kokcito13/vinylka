<?php

class Application_Model_Kernel_User {

    /**
     * @var integer $id идентификатор
     */
    public $id = null;

    /**
     * @var integer $roleId идентификатор роли
     */
    public $roleId = null;

    /**
     * @var integer $status статус
     */
    public $status = null;

    /**
     * @var string $login логин
     */
    public $login = null;

    /**
     * @var string $email е-мейл
     */
    public $email = null;

    /**
     * @var string $fullName полное имя
     */
    public $fullName = null;

    const STATUS_NOT_ACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BLOCKED = 2;
    const STATUS_REMOVED = 3;


    const ERROR_USER_NOT_FOUND = 'Пользователь не найден';
    const ERROR_LOGIN_EXISTS = 'Такой логин уже существует';
    const ERROR_INVALID_LOGIN = 'Неправильный формат логина';
    const ERROR_EMAIL_EXISTS = 'Такой e-mail уже существует';
    const ERROR_INVALID_EMAIL = 'Неправильный формат e-mail';
    const ERROR_INVALID_PASSWORD = 'Неправильный формат пароля';

    /**
     * Конструктор
     * 
     * @name __construct
     * @access public
     * @param integer $id
     * @param integer $roleId
     * @param integer $status
     * @param string $login
     * @param string $email
     * @param string $fullName
     * @return void
     */
    public function __construct($id, $roleId, $status, $login, $email, $fullName) {
        $this->id = $id;
        $this->roleId = $roleId;
        $this->status = $status;
        $this->login = $login;
        $this->email = $email;
        $this->fullName = $fullName;
    }

    /**
     * Возвращает объект пользователя по идентификатору
     * 
     * @name getById
     * @access public
     * @static
     * @param integer $id идентификатор пользователя
     * @return Application_Model_Kernel_User
     */
    public static function getById($id) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $select = $db->select();
        $select->from('users', '*');
        $select->where('users.id = ?', intval($id));
        if (!$row = $db->fetchRow($select))
            throw new exception(self::ERROR_USER_NOT_FOUND);
        return new Application_Model_Kernel_User($row->id, $row->roleId, $row->status, $row->login, $row->email, $row->fullName);
    }

    /**
     * Возвращает педжинатор по пользователям
     * 
     * @name getList
     * @access public
     * @static
     * @param integer $page номер страницы
     * @param integer $orderField поле сортировки
     * @param integer $orderRoute направление сортировки
     * @return Zend_Paginator
     */
    public static function getList($page, $orderField, $orderRoute) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $select = $db->select();
        $select->from('users', array('id', 'login', 'fullName', 'roleId', 'status', 'email'));
//		$select->joinLeft('roles','roles.id = users.roleId',array('roleName'=>'name'));
        $select->order($orderField . ' ' . $orderRoute);
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);
        return $paginator;
    }

    /**
     * Проверяет существование логина
     * 
     * @name checkLogin
     * @access public
     * @static
     * @param string $login логин
     * @param integer $exceptId исключающий идентификатор пользователя
     * @return boolean
     */
    public static function checkLoginExists($login, $exceptId = null) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $select = $db->select();
        $select->from('users', array('id'));
        $select->where('users.login = ?', $login);
        if (!is_null($exceptId)) {
            $select->where('users.id != ?', $exceptId);
        }
        return ($result = $db->fetchRow($select)) ? true : false;
    }

    /**
     * Проверяет существование e-mail
     * 
     * @name checkLogin
     * @access public
     * @static
     * @param string $email e-mail
     * @param integer $exceptId исключающий идентификатор пользователя
     * @return boolean
     */
    public static function checkEmailExists($email, $exceptId = null) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $select = $db->select();
        $select->from('users', array('id'));
        $select->where('users.email = ?', $email);
        if (!is_null($exceptId)) {
            $select->where('users.id != ?', $exceptId);
        }
        return ($result = $db->fetchRow($select)) ? true : false;
    }

    /**
     * Сохраняет пользователя
     * 
     * @name checkLogin
     * @access public
     * @param string $login
     * @return boolean
     */
    public function save($password = null) {
        $e = new Application_Model_Kernel_Exception();
        $this->login = trim($this->login);
        $this->email = strtolower(trim($this->email));
        
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        if (is_null($this->id)) {
            $data = array(
                'status' => self::STATUS_ACTIVE,
                'roleId' => $this->roleId,
                'email' => $this->email,
                'login' => $this->login,
                'password' => sha1($password),
                'fullName' => $this->fullName,
            );
            $db->insert('users', $data);
            $this->id = $db->lastInsertId();
        } else {
            $data = array(
                'status' => $this->status,
                'roleId' => $this->roleId,
                'email' => $this->email,
                'login' => $this->login,
                'fullName' => $this->fullName
            );
            if (!is_null($password))
                $data['password'] = sha1($password);
            $db->update('users', $data, 'id = ' . $this->id);
        }
    }
    
    
    public function delete() {
        $db = Zend_Registry::get('db');
        $db->delete('users', "id = {$this->id}");
    }

}

?>
