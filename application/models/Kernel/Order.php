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

    public function __construct($id, $id_product, $price_user, $name, $phone, $text,
                                $price_current, $delivery_type, $categories = array(),
                                $types = array(), $created_at = false)
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

        if (!$this->created_at) {
            $this->created_at = time();
        }
    }

    public function setJson($part)
    {
        return json_encode($part);
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
            'created_at'    => $this->created_at
        );
        $db = Zend_Registry::get('db');
        if (is_null($this->idAphorism)) {

            $db->insert('order', $data);
            $this->id = $db->lastInsertId();
        }
        else {
            $this->getContentManager()->saveContentData();
            $db->update('order', $data, 'id = ' . (int)$this->id);
        }
    }
}