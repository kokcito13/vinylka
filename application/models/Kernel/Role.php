<?php
class Application_Model_Kernel_Role{
	public $id = null;
	public $name = null;
	
	
	public function __construct($id,$name){
		$this->id = $id;
		$this->name = $name;
	}
	
	public static function getList($notGuest = false){
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('roles','*');
		if($notGuest) $select->where('roles.id != ?',1);
		$return = array();
		if (($result = $db->fetchAll($select)) !== false) {
			foreach($result as $row){
				$return[] = new Application_Model_Kernel_Role($row->id,$row->name);
			}
		}		
		return $return;
	}
}
?>
