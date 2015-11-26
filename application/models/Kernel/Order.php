<?php
class Application_Model_Kernel_Order
{

    protected $id;
    protected $id_product;
    protected $name;
    protected $phone;

    /**
     * @var null|string
     */
    protected $email = null;

    protected $text;
    protected $price_user;
    protected $price_current;
    protected $delivery_type;
    protected $categories;

    /**
     * @var array
     */
    protected $types = array();

    protected $created_at;
    protected $status;

    protected $product = null;

    const STATUS_GOOD = 1;
    const STATUS_FAIL = 2;
    const STATUS_NOT_DONE = 3;
    const STATUS_WORK = 4;
    const STATUS_SEND = 5;

    public function __construct($id, $id_product, $price_user, $name, $phone, $text,
                                $price_current, $delivery_type, $categories = array(),
                                $types = array(), $created_at = false, $status = null, $email = null)
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
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

    public static function getSelf($data)
    {
        $data->categories = str_replace('"', '', $data->categories);
        $data->categories = str_replace('\\', '', $data->categories);

        $data->types = str_replace('"', '', $data->types);
        $data->types = str_replace('\\', '', $data->types);

        $categories = json_decode($data->categories, true);
        $types = json_decode($data->types, true);

        $categories = is_array($categories)?$categories:array();
        $types = is_array($types)?$types:array();

        return new self($data->id, $data->id_product, $data->price_user, $data->name, $data->phone, $data->text,
                        $data->price_current, $data->delivery_type, $categories,
                        $types, $data->created_at, $data->status, $data->email);
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
        return (int)$this->status;
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

    public function checkDate()
    {
        $curentD = new \DateTime(date('d.m.Y',$this->created_at));
        $nowD = new \DateTime('now');

        $interval = $nowD->diff($curentD);
        return $interval->days;
    }

    public function getTextStatus()
    {
        $text = 'Новый';
        switch($this->getStatus()) {
            case self::STATUS_GOOD:
                $text = 'Успешно закрыта';
                break;
            case self::STATUS_WORK:
                $text = 'В работе';
                break;
            case self::STATUS_SEND:
                $text = 'Отправленно';
                break;
            case self::STATUS_FAIL:
                $text = 'отказ';
                break;
            case self::STATUS_NOT_DONE:
                $text = 'не обработан';
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
            'email'         => $this->email,
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

    public static function getList($page = false, $onPage = false, $statistic = false, $where = false)
    {
        $return = new stdClass();
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('order');

        if ($where) {
            $select->where($where);
        }

        if ($statistic)
            $select->order('id_product');
        $select->order('id DESC');

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

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function setDeliveryType($delivery)
    {
        $this->delivery_type = $delivery;

        return $this;
    }

    public function setUserPrice($price)
    {
        $this->price_user = $price;

        return $this;
    }

    /**
     * @param string|null $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function getJson($json)
    {
        return json_decode($json, true);
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    public static function getStatisticByMonth($status = self::STATUS_GOOD)
    {
        $sql = "SELECT YEAR( new.Date ) as years , MONTHNAME( new.Date ) as mounths, SUM( price_user ) as prices
                FROM (
                  SELECT DATE_FORMAT( FROM_UNIXTIME(  `created_at` ) ,  '%Y-%m-%d %H:%i:%s' ) AS DATE,  `price_user`
                  FROM  `order`
                  WHERE status = ".$status."
                  and YEAR(DATE_FORMAT( FROM_UNIXTIME(  `created_at` ) ,  '%Y-%m-%d %H:%i:%s' )) = YEAR(CURDATE())
                ) new
                GROUP BY YEAR( new.Date ) , MONTH( new.Date )
                ORDER BY years
                ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $results = $db->fetchAll($sql);

        return $results;
    }
}