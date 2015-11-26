<?php

class Application_Model_Kernel_Buyer
{

    /**
     * @var integer $id идентификатор
     */
    private $id = null;

    /**
     * @var string $email е-мейл
     */
    private $email = null;

    /**
     * @var string $phone
     */
    private $phone = null;

    /**
     * @var string $fullName полное имя
     */
    private $fullName = null;

    /**
     * @var int
     */
    private $numberOrders = 0;

    private $orders = array();

    public function __construct($id = null, $email = '', $phone = '', $fullName = '', $numberOrders = 0)
    {
        $this->id = $id;
        $this->email = $email;
        $this->phone = $phone;
        $this->fullName = $fullName;
        $this->numberOrders = $numberOrders;
    }


    public static function getList()
    {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $select = $db->select()

        ->from(array('o' => 'order'), array('phone', 'name', 'email', 'COUNT(*) as counts'))
        ->group('o.phone')
        ->order('counts DESC')
        ;


        $results = array();
        $fetchAll = $db->fetchAll($select);

        foreach ($fetchAll as $item) {
            $results[] = new self(null, $item->email, $item->phone, $item->name, $item->counts);
        }

        return $results;
    }

    public static function getByPhone($phone)
    {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $select = $db->select()

            ->from(array('o' => 'order'), array('phone', 'name', 'email', 'COUNT(*) as counts'))
            ->where("o.phone LIKE '".$phone."'")
            ->group('o.phone')
            ->order('counts DESC')
        ;

        $result = null;
        $fetchAll = $db->fetchAll($select);

        foreach ($fetchAll as $item) {
            $result = new self(null, $item->email, $item->phone, $item->name, $item->counts);
        }

        return $result;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getFullName()
    {
        return $this->fullName;
    }

    public function getNumberOrders()
    {
        return $this->numberOrders;
    }

    public function getOrders()
    {
        if (empty($this->orders)) {
            $phone = str_replace(' ', '%', $this->getPhone());
            $this->orders = Application_Model_Kernel_Order::getList(false, false, false, "phone LIKE '%".$phone."%'");
        }

        return $this->orders;
    }
}