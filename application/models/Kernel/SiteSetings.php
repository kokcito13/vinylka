<?php
class Application_Model_Kernel_SiteSetings
{

    protected $id;
    protected $idPhoto1;

    protected $rate;
    protected $minPrice;

    protected $phone;
    protected $email;


    protected $photo1 = null;

    public function __construct($id, $idPhoto1, $rate, $minPrice, $phone, $email)
    {
        $this->id = $id;
        $this->idPhoto1 = $idPhoto1;
        $this->rate = $rate;
        $this->minPrice = $minPrice;
        $this->phone = $phone;
        $this->email = $email;
    }


    public function getIdPhoto1()
    {
        return $this->idPhoto1;
    }

    public function getPhoto1()
    {
        if (is_null($this->photo1))
            $this->photo1 = Application_Model_Kernel_Photo::getById($this->idPhoto1);

        return $this->photo1;
    }

    public function setPhoto1(Application_Model_Kernel_Photo &$photo1)
    {
        $this->photo1 = $photo1;

        return $this;
    }

    public function setIdPhoto1($idPhoto1)
    {
        $this->idPhoto1 = $idPhoto1;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function getMinPrice()
    {
        return $this->minPrice;
    }

    public function setMinPrice($value)
    {
        $this->minPrice = (int)$value;
    }

    public function setRate($value)
    {
        $this->rate = (int)$value;
    }

    public function getPhone()
    {

        return $this->phone;
    }

    public function setPhone($value)
    {
        $this->phone = $value;

        return $this;
    }

    public function getEmail()
    {

        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;

        return $this;
    }

    public function validate()
    {
//        if ($this->getName() === '') {
//            throw new Exception('Enter block name');
//        }
//        if (strlen($this->getName()) <= 3) {
//            throw new Exception('Block name must me more then 3 letter');
//        }
    }

    /**
     * Save block data
     * @access public
     * @return void
     */
    public function save()
    {
        $data = array(
            'idPhoto1'     => $this->idPhoto1,
            'rate'         => $this->rate,
            'minPrice' => $this->minPrice,
            'phone' => $this->phone,
            'email' => $this->email
        );
        $db = Zend_Registry::get('db');
        $db->update('site_setings', $data, 'id = ' . $this->getId());
    }

    public static function getBy()
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('site_setings');
        $select->where('site_setings.id = 1');
        $select->limit(1);
        if (($block = $db->fetchRow($select)) !== false) {
            return new self($block->id, $block->idPhoto1, $block->rate, $block->minPrice, $block->phone, $block->email);
        }
        else {
            throw new Exception('Table NOT FOUND');
        }
    }
}