<?php
class Application_Model_Kernel_Remarketing
{
    protected $ip2long;
    protected $product_id;

    private $product = null;

    public function __construct($ip2long, $product_id)
    {
        $this->ip2long    = $ip2long;
        $this->product_id = $product_id;
    }

    public function getIp2long()
    {
        return $this->ip2long;
    }

    public function setIp($ip)
    {
        $this->ip2long = ip2long($ip);

        return $this;
    }

    public function setProductId($id)
    {
        $this->product_id = $id;

        return $this;
    }

    public static function getSelf($data)
    {
        return new self($data->ip2long, $data->product_id);
    }

    public function getProduct()
    {
        if (is_null($this->product) && !empty($this->product_id)) {
            $this->product = Application_Model_Kernel_Product::getById($this->product_id);
        }

        return $this->product;
    }

    public static function getByIp($ip)
    {
        $ip2long = ip2long($ip);
        $db      = Zend_Registry::get('db');

        $select = $db->select()->from('remarketing');
        $select->where('remarketing.ip2long = ?', (int)$ip2long);
        $select->limit(1);
        if (false !== ($data = $db->fetchRow($select))) {
            return self::getSelf($data);
        }

        return null;
    }

    public function save()
    {
        $data = array (
            'ip2long'    => $this->ip2long,
            'product_id' => $this->product_id
        );
        $db   = Zend_Registry::get('db');
        $db->insert('remarketing', $data);
    }

    public function update()
    {
        $data = array (
            'ip2long'    => $this->ip2long,
            'product_id' => $this->product_id
        );
        $db   = Zend_Registry::get('db');
        $db->update('remarketing', $data, 'ip2long = ' . $this->ip2long);
    }
}