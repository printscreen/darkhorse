<?php
class Model_Stat extends Model_Base_Db
{
    protected $_shirtSex;
    protected $_shirtSize;
    protected $_shirtType;
    protected $_quantity;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'shirtSex' => null,
            'shirtSize' => null,
            'shirtType' => null,
            'quantity' => null,
            'db' => null,
            ), $options);
        parent::__construct($settings['db']);
        $this->_shirtSex = $settings['shirtSex'];
        $this->_shirtSize = $settings['shirtSize'];
        $this->_shirtType = $settings['shirtType'];
        $this->_quantity = $settings['quantity'];
    }

    public function loadRecord($record)
    {
        $this->_shirtSex = $record->shirt_sex;
        $this->_shirtSize = $record->shirt_size;
        $this->_shirtType = $record->shirt_type;
        $this->_quantity = $record->quantity;
    }

    //Setters
    public function setShirtSex($shirtSex){$this->_shirtSex = $shirtSex; return $this;}
    public function setShirtSize($shirtSize){$this->_shirtSize = $shirtSize; return $this;}
    public function setShirtType($shirtType){$this->_shirtType = $shirtType; return $this;}
    public function setQuantity($quantity){$this->_quantity = $quantity; return $this;}

    //Getters
    public function getShirtSex(){return $this->_shirtSex;}
    public function getShirtSize(){return $this->_shirtSize;}
    public function getShirtType(){return $this->_shirtType;}
    public function getQuantity(){return $this->_quantity;}
}