<?php
class Model_Customer extends Model_Base_Db
{
    protected $_customerId;
    protected $_name;
    protected $_active;
    protected $_total;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'customerId' => null,
            'name' => null,
            'active' => null,
            'db' => null,
            ), $options);
        parent::__construct($settings['db']);
        $this->_customerId = $settings['customerId'];
        $this->_name = $settings['name'];
        $this->_active = $settings['active'];
    }

    public function loadRecord($record)
    {
        $this->_customerId = $record->customer_id;
        $this->_name = $record->name;
        $this->_active = $record->active;
        $this->_total = $record->total;
    }

    public function load()
    {
        $where = 'WHERE true';
        $binds = array();
        if(!empty($this->_customerId) && is_numeric($this->_customerId)) {
            $where .= ' AND customer_id = :customerId';
            $binds[':customerId'] = $this->_customerId;
        } else if(!empty($this->_name)) {
            $where .= ' AND lower(name) = :name';
            $binds[':name'] = strtolower($this->_name);
        } else {
            throw new Zend_Exception("No customer id supplied");
        }

        $sql = "
            SELECT
                customer_id
              , name
              , active
              , 1 AS total
            FROM customer $where LIMIT 1
        ";
        $query = $this->_db->prepare($sql);
        $query->execute($binds);
        $result = $query->fetchAll();

        if(!$result || count($result) != 1) {
            return false;
        }

        $this->loadRecord($result[0]);
        return true;
    }

    public function insert()
    {
        $sql = "INSERT INTO customer (
                    name
                  , active
                )
                VALUES (
                    :name
                  , :active
                )";
        $query = $this->_db->prepare($sql);

        $active = $this->convertFromBoolean($this->_active);

        $query->bindParam(':name', $this->_name, PDO::PARAM_STR);
        $query->bindParam(':active', $active, PDO::PARAM_BOOL);

        $result = $query->execute();

        if(!$result) {
            return false;
        }
        $this->_customerId = $this->_db->lastInsertId('customer','customer_id');

        return true;
    }

    public function update()
    {
        if(empty($this->_customerId) || !is_numeric($this->_customerId)) {
            throw new Zend_Exception('No customer id supplied');
        }
        $sql = "UPDATE customer SET
                    name = COALESCE(:name, name)
                  , active = COALESCE(:active, active)
                  WHERE customer_id = :customerId;
                ";
        $query = $this->_db->prepare($sql);

        $customerId = $this->convertToInt($this->_customerId);
        $active = $this->convertFromBoolean($this->_active);

        $query->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $query->bindParam(':name', $this->_name , PDO::PARAM_STR);
        $query->bindParam(':active', $active, PDO::PARAM_BOOL);
        $result = $query->execute();

        if(!$result) {
            return false;
        }
        return true;
    }

    //Setters
    public function setCustomerId($customerId){$this->_customerId = $customerId; return $this;}
    public function setName($name){$this->_name = $name; return $this;}
    public function setActive($active){$this->_active = $active; return $this;}

    //Getters
    public function getCustomerId(){return $this->_customerId;}
    public function getName(){return $this->_name;}
    public function getActive(){return $this->_active;}
    public function getTotal(){return $this->_total;}
}