<?php
class Model_Batch extends Model_Base_Db
{
    protected $_batchId;
    protected $_insertTs;
    protected $_name;
    protected $_customerId;
    protected $_customerName;
    protected $_contactName;
    protected $_contactPhoneNumber;
    protected $_contactEmail;
    protected $_street;
    protected $_suiteApt;
    protected $_city;
    protected $_state;
    protected $_postalCode;
    protected $_active;
    protected $_total;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'batchId' => null,
            'name' => null,
            'customerId' => null,
            'contactName' => null,
            'contactPhoneNumber' => null,
            'contactEmail' => null,
            'street' => null,
            'suiteApt' => null,
            'city' => null,
            'state' => null,
            'postalCode' => null,
            'active' => null,
            'db' => null,
            ), $options);
        parent::__construct($settings['db']);
        $this->_batchId = $settings['batchId'];
        $this->_name = $settings['name'];
        $this->_customerId = $settings['customerId'];
        $this->_contactName = $settings['contactName'];
        $this->_contactPhoneNumber = $settings['contactPhoneNumber'];
        $this->_contactEmail = $settings['contactEmail'];
        $this->_street = $settings['street'];
        $this->_suiteApt = $settings['suiteApt'];
        $this->_city = $settings['city'];
        $this->_state = $settings['state'];
        $this->_postalCode = $settings['postalCode'];
        $this->_active = $settings['active'];
    }

    public function loadRecord($record)
    {
        $this->_batchId = $record->batch_id;
        $this->_insertTs = $record->insert_ts;
        $this->_name = $record->name;
        $this->_customerId = $record->customer_id;
        $this->_customerName = $record->customer_name;
        $this->_contactName = $record->contact_name;
        $this->_contactPhoneNumber = $record->contact_phone;
        $this->_contactEmail = $record->contact_email;
        $this->_street = $record->street;
        $this->_suiteApt = $record->suite_apt;
        $this->_city = $record->city;
        $this->_state = $record->state;
        $this->_postalCode = $record->postal_code;
        $this->_active = $record->active;
        $this->_total = $record->total;
    }

    public function load()
    {
        $where = 'WHERE true';
        $binds = array();
        if(!empty($this->_batchId) && is_numeric($this->_batchId)) {
            $where .= ' AND batch_id = :batchId';
            $binds[':batchId'] = $this->_batchId;
        } else if(!empty($this->_name)) {
            $where .= ' AND name = :name';
            $binds[':name'] = $this->_name;
        } else {
            throw new Zend_Exception("No batch id or name supplied");
        }

        $sql = "
            SELECT
                b.batch_id
              , b.insert_ts
              , b.name
              , b.customer_id
              , c.name as customer_name
              , b.contact_name
              , b.contact_phone
              , b.contact_email
              , b.street
              , b.suite_apt
              , b.city
              , b.state
              , b.postal_code
              , b.active
              , 1 AS total
            FROM batch b
            INNER JOIN customer c ON b.customer_id = c.customer_id
            $where LIMIT 1
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
        $sql = "INSERT INTO batch (
                    name
                  , customer_id
                  , contact_name
                  , contact_phone
                  , contact_email
                  , street
                  , suite_apt
                  , city
                  , state
                  , postal_code
                  , active
                )
                VALUES (
                    :name
                  , :customerId
                  , :contactName
                  , :contactPhone
                  , :contactEmail
                  , :street
                  , :suiteApt
                  , :city
                  , :state
                  , :postalCode
                  , :active
                )";
        $query = $this->_db->prepare($sql);

        $active = $this->convertFromBoolean($this->_active);
        $customerId = $this->convertToInt($this->_customerId);

        $query->bindParam(':name', $this->_name, PDO::PARAM_STR);
        $query->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $query->bindParam(':contactName', $this->_contactName, PDO::PARAM_STR);
        $query->bindParam(':contactPhone', $this->_contactPhoneNumber, PDO::PARAM_STR);
        $query->bindParam(':contactEmail', $this->_contactEmail, PDO::PARAM_STR);
        $query->bindParam(':street', $this->_street, PDO::PARAM_STR);
        $query->bindParam(':suiteApt', $this->_suiteApt, PDO::PARAM_STR);
        $query->bindParam(':city', $this->_city, PDO::PARAM_STR);
        $query->bindParam(':state', $this->_state, PDO::PARAM_STR);
        $query->bindParam(':postalCode', $this->_postalCode, PDO::PARAM_STR);
        $query->bindParam(':active', $active, PDO::PARAM_BOOL);

        $result = $query->execute();

        if(!$result) {
            return false;
        }
        $this->_batchId = $this->_db->lastInsertId('batch','batch_id');

        return true;
    }

    public function update()
    {
        if(empty($this->_batchId) || !is_numeric($this->_batchId)) {
            throw new Zend_Exception('No batch id supplied');
        }
        $sql = "UPDATE batch SET
                    name = COALESCE(:name, name)
                  , customer_id = COALESCE(:customerId, customer_id)
                  , contact_name = COALESCE(:contactName, contact_name)
                  , contact_phone = COALESCE(:contactPhone, contact_phone)
                  , contact_email = COALESCE(:contactEmail, contact_email)
                  , street = COALESCE(:street, street)
                  , suite_apt = COALESCE(:suiteApt, suite_apt)
                  , city = COALESCE(:city, city)
                  , state = COALESCE(:state, state)
                  , postal_code = COALESCE(:postalCode, postal_code)
                  , active = COALESCE(:active, active)
                  WHERE batch_id = :batchId;
                ";
        $query = $this->_db->prepare($sql);

        $batchId = $this->convertToInt($this->_batchId);
        $customerId = $this->convertToInt($this->_customerId);
        $active = $this->convertFromBoolean($this->_active);

        $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $query->bindParam(':name', $this->_name, PDO::PARAM_STR);
        $query->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $query->bindParam(':contactName', $this->_contactName, PDO::PARAM_STR);
        $query->bindParam(':contactPhone', $this->_contactPhoneNumber, PDO::PARAM_STR);
        $query->bindParam(':contactEmail', $this->_contactEmail, PDO::PARAM_STR);
        $query->bindParam(':street', $this->_street, PDO::PARAM_STR);
        $query->bindParam(':suiteApt', $this->_suiteApt, PDO::PARAM_STR);
        $query->bindParam(':city', $this->_city, PDO::PARAM_STR);
        $query->bindParam(':state', $this->_state, PDO::PARAM_STR);
        $query->bindParam(':postalCode', $this->_postalCode, PDO::PARAM_STR);
        $query->bindParam(':active', $active, PDO::PARAM_BOOL);
        $result = $query->execute();

        if(!$result) {
            return false;
        }
        return true;
    }

    //Setters
    public function setbatchId($batchId){$this->_batchId = $batchId; return $this;}
    public function setName($name){$this->_name = $name; return $this;}
    public function setCustomerId($customerId){$this->_customerId = $customerId; return $this;}
    public function setContactName($contactName){$this->_contactName = $contactName; return $this;}
    public function setContactPhoneNumber($contactPhoneNumber){$this->_contactPhoneNumber = $contactPhoneNumber; return $this;}
    public function setContactEmail($contactEmail){$this->_contactEmail = $contactEmail; return $this;}
    public function setStreet($street){$this->_street = $street; return $this;}
    public function setSuiteApt($suiteApt){$this->_suiteApt = $suiteApt; return $this;}
    public function setCity($city){$this->_city = $city; return $this;}
    public function setState($state){$this->_state = $state; return $this;}
    public function setZip($postalCode){$this->_postalCode = $postalCode; return $this;}
    public function setActive($active){$this->_active = $active; return $this;}

    //Getters
    public function getbatchId(){return $this->_batchId;}
    public function getInsertTs(){return $this->_insertTs;}
    public function getName(){return $this->_name;}
    public function getCustomerId(){return $this->_customerId;}
    public function getCustomerName(){return $this->_customerName;}
    public function getContactName(){return $this->_contactName;}
    public function getContactPhoneNumber(){return $this->_contactPhoneNumber;}
    public function getContactEmail(){return $this->_contactEmail;}
    public function getStreet(){return $this->_street;}
    public function getSuiteApt(){return $this->_suiteApt;}
    public function getCity(){return $this->_city;}
    public function getState(){return $this->_state;}
    public function getZip(){return $this->_postalCode;}
    public function getActive(){return $this->_active;}
    public function getTotal(){return $this->_total;}
}