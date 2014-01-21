<?php
class Application_Model_Kernel_Order
{

    protected $id;
    protected $id_product;
    protected $name;
    protected $phone;
    protected $text;
    protected $price_user;
    protected $price_current;
    protected $delivery_type;
    protected $categories;
    protected $types;
    protected $created_at;
    protected $status;

    protected $product = null;

    const STATUS_GOOD = 1;

    public function __construct($id, $id_product, $price_user, $name, $phone, $text,
                                $price_current, $delivery_type, $categories = array(),
                                $types = array(), $created_at = false, $status = null)
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->text = $text;

        $this->id = $id;
        $this->id_product = $id_product;
        $this->price_user = $price_user;

        $this->price_current = $price_current;
        $this->delivery_type = $delivery_type;
        $this->categories = $categories;

        $this->types = $types;
        $this->created_at = $created_at;
        $this->status = $status;

        if (!$this->created_at) {
            $this->created_at = time();
        }
    }

    public static function getSelf($data) {
        return new self($data->id, $data->id_product, $data->price_user, $data->name, $data->phone, $data->text,
                        $data->price_current, $data->delivery_type, $data->categories,
                        $data->types, $data->created_at, $data->status);
    }


    public function getId(){
        return $this->id;
    }

    public function getIdProduct(){
        return $this->id_product;
    }

    public function getName(){
        return $this->name;
    }

    public function getPhone(){
        return $this->phone;
    }

    public function getText(){
        return $this->text;
    }

    public function getPriceUser(){
        return $this->price_user;
    }

    public function getStatus(){
        return $this->status;
    }

    public function getDeliveryType(){
        return $this->delivery_type;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    public function getNormalData(){
        return date('d.m.Y H:i', $this->created_at);
    }

    public function getTextStatus()
    {
        $text = 'new order';

        switch($this->getStatus()) {
            case self::STATUS_GOOD:
                $text = 'good done order';
                break;
        }

        return $text;
    }

    public function setJson($part) {
        return json_encode($part);
    }

    public function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = Application_Model_Kernel_Product::getById($this->getIdProduct());
        }

        return $this->product;
    }

    public function save()
    {
        $data = array(
            'id'            => $this->id,
            'id_product'    => $this->id_product,
            'price_user'    => $this->price_user,

            'name'          => $this->name,
            'phone'         => $this->phone,
            'text'          => $this->text,

            'price_current' => $this->price_current,
            'delivery_type' => $this->delivery_type,
            'categories'    => $this->setJson($this->categories),

            'types'         => $this->setJson($this->types),
            'created_at'    => $this->created_at,
            'status'    => $this->status
        );
        $db = Zend_Registry::get('db');
        if (is_null($this->id)) {
            $db->insert('order', $data);
            $this->id = $db->lastInsertId();
        } else {
            $db->update('order', $data, 'id = ' . (int)$this->id);
        }
    }

    public static function getList($page = false, $onPage = false)
    {
        $return = new stdClass();
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('order');

        if ($page !== false) {
            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($onPage);
            $paginator->setPageRange(5);
            $paginator->setCurrentPageNumber($page);
            $return->paginator = $paginator;
        } else {
            $return->paginator = $db->fetchAll($select);
        }

        $return->data = array();
        $i = 0;
        foreach ($return->paginator as $data) {
            $return->data[$i] = self::getSelf($data);
            $i++;
        }

        return $return;
    }

    public static function getById($id)
    {
        $id = (int)$id;
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('order');
        $select->where('id = ?', $id);
        $select->limit(1);
        if (($data = $db->fetchRow($select)) !== false) {
            return self::getSelf($data);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
    }
}