<?php
class Model_Prepare extends Model_Base_Db
{
    protected $_batchId;
    protected $_shirtSex;
    protected $_shirtSize;
    protected $_shirtType;
    protected $_weight;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'batchId' => null,
            'shirtSex' => null,
            'shirtSize' => null,
            'shirtType' => null,
            'weight' => null,
            'db' => null,
            ), $options);
        parent::__construct($settings['db']);
        $this->_batchId = $settings['batchId'];
        $this->_shirtSex = $settings['shirtSex'];
        $this->_shirtSize = $settings['shirtSize'];
        $this->_shirtType = $settings['shirtType'];
        $this->_weight = $settings['weight'];
    }

    public function loadRecord($record)
    {
        $this->_batchId = $record->batch_id;
        $this->_shirtSex = $record->shirt_sex;
        $this->_shirtSize = $record->shirt_size;
        $this->_shirtType = $record->shirt_type;
        $this->_weight = $record->weight;
    }

    public function updateType($oldValue)
    {
        if(empty($this->_batchId) || !is_numeric($this->_batchId)) {
            throw new Zend_Exception('No batch id supplied');
        }
        $sql = "UPDATE recipient SET
                    shirt_type = UPPER(:type)
                  WHERE batch_id = :batchId
                  AND shirt_sex = :shirtSex
                  AND shirt_size = :shirtSize
                  AND shirt_type = :oldValue;
                ";
        $query = $this->_db->prepare($sql);

        $batchId = $this->convertToInt($this->_batchId);

        $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $query->bindParam(':shirtSex', $this->_shirtSex, PDO::PARAM_STR);
        $query->bindParam(':shirtSize', $this->_shirtSize, PDO::PARAM_STR);
        $query->bindParam(':type', $this->_shirtType, PDO::PARAM_STR);
        $query->bindParam(':oldValue', $oldValue, PDO::PARAM_STR);
        $result = $query->execute();

        if(!$result) {
            return false;
        }
        return true;
    }

    public function updateWeight()
    {
        if(empty($this->_batchId) || !is_numeric($this->_batchId)) {
            throw new Zend_Exception('No batch id supplied');
        }
        $sql = "UPDATE recipient SET
                    weight = :weight
                  WHERE batch_id = :batchId
                  AND shirt_sex = :shirtSex
                  AND shirt_size = :shirtSize
                  AND shirt_type = :shirtType;
                ";
        $query = $this->_db->prepare($sql);

        $batchId = $this->convertToInt($this->_batchId);
        $weight = $this->convertToInt($this->_weight);

        $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $query->bindParam(':weight', $weight, PDO::PARAM_INT);
        $query->bindParam(':shirtSex', $this->_shirtSex , PDO::PARAM_STR);
        $query->bindParam(':shirtSize', $this->_shirtSize , PDO::PARAM_STR);
        $query->bindParam(':shirtType', $this->_shirtType , PDO::PARAM_STR);
        $result = $query->execute();

        if(!$result) {
            return false;
        }
        return true;
    }

    //Setters
    public function setBatchId($batchId){$this->_batchId = $batchId; return $this;}
    public function setShirtSex($shirtSex){$this->_shirtSex = $shirtSex; return $this;}
    public function setShirtSize($shirtSize){$this->_shirtSize = $shirtSize; return $this;}
    public function setShirtType($shirtType){$this->_shirtType = $shirtType; return $this;}
    public function setWeight($weight){$this->_weight = $weight; return $this;}

    //Getters
    public function getBatchId(){return $this->_batchId;}
    public function getShirtSex(){return $this->_shirtSex;}
    public function getShirtSize(){return $this->_shirtSize;}
    public function getShirtType(){return $this->_shirtType;}
    public function getWeight(){return $this->_weight;}
}