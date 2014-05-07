<?php
class Model_Recipient extends Model_Base_Db
{
    protected $_recipientId;
    protected $_batchId;
    protected $_email;
    protected $_firstName;
    protected $_lastName;
    protected $_addressLineOne;
    protected $_addressLineTwo;
    protected $_city;
    protected $_state;
    protected $_postalCode;
    protected $_verifiedAddress;
    protected $_shipTs;
    protected $_insertTs;
    protected $_trackingNumber;
    protected $_shirtSex;
    protected $_shirtSize;
    protected $_shirtType;
    protected $_quantity;
    protected $_total;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'recipientId' => null,
            'batchId' => null,
            'email' => null,
            'firstName' => null,
            'lastName' => null,
            'addressLineOne' => null,
            'addressLineTwo' => null,
            'city' => null,
            'state' => null,
            'postalCode' => null,
            'verifiedAddress' => null,
            'shipTs' => null,
            'trackingNumber' => null,
            'shirtSex' => null,
            'shirtSize' => null,
            'shirtType' => null,
            'quantity' => null,
            'db' => null,
            ), $options);
        parent::__construct($settings['db']);
        $this->_recipientId = $settings['recipientId'];
        $this->_batchId = $settings['batchId'];
        $this->_email = $settings['email'];
        $this->_firstName = $settings['firstName'];
        $this->_lastName = $settings['lastName'];
        $this->_addressLineOne = $settings['addressLineOne'];
        $this->_addressLineTwo = $settings['addressLineTwo'];
        $this->_city = $settings['city'];
        $this->_state = $settings['state'];
        $this->_postalCode = $settings['postalCode'];
        $this->_verifiedAddress = $settings['verifiedAddress'];
        $this->_shipTs = $settings['shipTs'];
        $this->_trackingNumber = $settings['trackingNumber'];
        $this->_shirtSex = $settings['shirtSex'];
        $this->_shirtSize = $settings['shirtSize'];
        $this->_shirtType = $settings['shirtType'];
        $this->_quantity = $settings['quantity'];
    }

    public function loadRecord($record)
    {
        $this->_recipientId = $record->recipient_id;
        $this->_batchId = $record->batch_id;
        $this->_email = $record->email;
        $this->_firstName = $record->first_name;
        $this->_lastName = $record->last_name;
        $this->_addressLineOne = $record->address_line_one;
        $this->_addressLineTwo = $record->address_line_two;
        $this->_city = $record->city;
        $this->_state = $record->state;
        $this->_postalCode = $record->postal_code;
        $this->_verifiedAddress = $record->verified_address;
        $this->_insertTs = $record->insert_ts;
        $this->_shipTs = $record->ship_ts;
        $this->_trackingNumber = $record->tracking_number;
        $this->_shirtSex = $record->shirt_sex;
        $this->_shirtSize = $record->shirt_size;
        $this->_shirtType = $record->shirt_type;
        $this->_quantity = $record->quantity;
        $this->_total = $record->total;
    }

    public function load()
    {
        $where = 'WHERE true';
        $binds = array();
        if(is_numeric($this->_recipientId)) {
            $where .= ' AND r.recipient_id = :recipientId';
            $binds[':recipientId'] = array('value' => $this->_recipientId, 'type' => PDO::PARAM_INT);
        } else {
            throw new Zend_Exception("No recipient id supplied");
        }

        $sql = "SELECT
                r.recipient_id
              , r.batch_id
              , r.email
              , r.first_name
              , r.last_name
              , r.address_line_one
              , r.address_line_two
              , r.city
              , r.state
              , r.postal_code
              , r.verified_address
              , r.insert_ts
              , r.ship_ts
              , r.tracking_number
              , r.shirt_sex
              , r.shirt_size
              , r.shirt_type
              , r.quantity
              , 1 AS total
            FROM recipient r
            $where LIMIT 1
        ";

        $query = $this->_db->prepare($sql);
        $this->bind($query, $binds);
        $query->execute();

        $result = $query->fetchAll();

        if(!$result || count($result) != 1) {
            return false;
        }

        $this->loadRecord(current($result));
        return true;
    }

    public function insert()
    {
        $sql = "INSERT INTO recipient (
                    batch_id
                  , email
                  , first_name
                  , last_name
                  , address_line_one
                  , address_line_two
                  , city
                  , state
                  , postal_code
                  , verified_address
                  , ship_ts
                  , tracking_number
                  , shirt_sex
                  , shirt_size
                  , shirt_type
                  , quantity
                )
                VALUES (
                    :batchId
                  , :email
                  , :firstName
                  , :lastName
                  , :addressLineOne
                  , :addressLineTwo
                  , :city
                  , :state
                  , :postalCode
                  , COALESCE(:verifiedAddress, false)
                  , :shipTs
                  , :trackingNumber
                  , UPPER(:shirtSex)
                  , UPPER(:shirtSize)
                  , UPPER(:shirtType)
                  , :quantity
                )";

        $query = $this->_db->prepare($sql);

        $batchId = $this->convertToInt($this->_batchId);
        $quantity = $this->convertToInt($this->_quantity);
        $verifiedAddress = $this->convertFromBoolean($this->_verifiedAddress);

        $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $query->bindParam(':email', $this->_email, PDO::PARAM_STR);
        $query->bindParam(':firstName', $this->_firstName, PDO::PARAM_STR);
        $query->bindParam(':lastName', $this->_lastName, PDO::PARAM_STR);
        $query->bindParam(':addressLineOne', $this->_addressLineOne, PDO::PARAM_STR);
        $query->bindParam(':addressLineTwo', $this->_addressLineTwo, PDO::PARAM_STR);
        $query->bindParam(':city', $this->_city, PDO::PARAM_STR);
        $query->bindParam(':state', $this->_state, PDO::PARAM_STR);
        $query->bindParam(':postalCode', $this->_postalCode, PDO::PARAM_STR);
        $query->bindParam(':verifiedAddress', $verifiedAddress, PDO::PARAM_BOOL);
        $query->bindParam(':shipTs', $this->_shipTs, PDO::PARAM_STR);
        $query->bindParam(':trackingNumber', $this->_trackingNumber, PDO::PARAM_STR);
        $query->bindParam(':shirtSex', $this->_shirtSex, PDO::PARAM_STR);
        $query->bindParam(':shirtSize', $this->_shirtSize, PDO::PARAM_STR);
        $query->bindParam(':shirtType', $this->_shirtType, PDO::PARAM_STR);
        $query->bindParam(':quantity', $quantity, PDO::PARAM_INT);


        $result = $query->execute();

        if(!$result) {
            return false;
        }
        $this->_recipientId = $this->_db->lastInsertId('recipient','recipient_id');

        return true;
    }

    public function update()
    {
        if(empty($this->_recipientId) || !is_numeric($this->_recipientId)) {
            throw new Zend_Exception('No recipient id supplied');
        }
        $sql = "UPDATE recipient SET
                    batch_id = COALESCE(:batchId, batch_id)
                  , email = COALESCE(:email, email)
                  , first_name = COALESCE(:firstName, first_name)
                  , last_name = COALESCE(:lastName, last_name)
                  , address_line_one = COALESCE(:addressLineOne, address_line_one)
                  , address_line_two = COALESCE(:addressLineTwo, address_line_two)
                  , city = COALESCE(:city, city)
                  , state = COALESCE(:state, state)
                  , postal_code = COALESCE(:postalCode, postal_code)
                  , verified_address = COALESCE(:verifiedAddress, verified_address)
                  , ship_ts = COALESCE(:shipTs, ship_ts)
                  , tracking_number = COALESCE(:trackingNumber, tracking_number)
                  , shirt_sex = COALESCE(UPPER(:shirtSex), shirt_sex)
                  , shirt_size = COALESCE(UPPER(:shirtSize), shirt_sex)
                  , shirt_type = COALESCE(UPPER(:shirtType), shirt_type)
                  , quantity = COALESCE(:quantity, quantity)
                  WHERE recipient_id = :recipientId;
                ";

        $query = $this->_db->prepare($sql);

        $recipientId = $this->convertToInt($this->_recipientId);
        $batchId = $this->convertToInt($this->_batchId);
        $quantity = $this->convertToInt($this->_quantity);
        $verifiedAddress = $this->convertFromBoolean($this->_verifiedAddress);

        $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $query->bindParam(':email', $this->_email, PDO::PARAM_STR);
        $query->bindParam(':firstName', $this->_firstName, PDO::PARAM_STR);
        $query->bindParam(':lastName', $this->_lastName, PDO::PARAM_STR);
        $query->bindParam(':addressLineOne', $this->_addressLineOne, PDO::PARAM_STR);
        $query->bindParam(':addressLineTwo', $this->_addressLineTwo, PDO::PARAM_STR);
        $query->bindParam(':city', $this->_city, PDO::PARAM_STR);
        $query->bindParam(':state', $this->_state, PDO::PARAM_STR);
        $query->bindParam(':postalCode', $this->_postalCode, PDO::PARAM_STR);
        $query->bindParam(':verifiedAddress', $verifiedAddress, PDO::PARAM_BOOL);
        $query->bindParam(':shipTs', $this->_shipTs, PDO::PARAM_STR);
        $query->bindParam(':trackingNumber', $this->_trackingNumber, PDO::PARAM_STR);
        $query->bindParam(':shirtSex', $this->_shirtSex, PDO::PARAM_STR);
        $query->bindParam(':shirtSize', $this->_shirtSize, PDO::PARAM_STR);
        $query->bindParam(':shirtType', $this->_shirtType, PDO::PARAM_STR);
        $query->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $query->bindParam(':recipientId', $recipientId, PDO::PARAM_INT);
        $result = $query->execute();

        if(!$result) {
            return false;
        }
        return true;
    }

    public function verify()
    {
        $verify = new Darkhorse_Usps_Stamps(array(
            'apiUsername' => Zend_Registry::get(USPS_API_USERNAME),
            'apiPassword' => Zend_Registry::get(USPS_API_PASSWORD)
        ));
        try {
            $response = $verify->verifyAddress(array(
                'firstName' => $this->_firstName,
                'lastName' => $this->_lastName,
                'address' => $this->_addressLineOne,
                'addressLineTwo' => $this->_addressLineTwo,
                'city' => $this->_city,
                'state' => $this->_state,
                'postalCode' => $this->_postalCode
            ));
        } catch(Zend_Exception $ze) {
            //Unable to verify
            return false;
        }

        $address = current($response['AddressValidateResponse']);
        $this->_addressLineOne = $address['Address2']; // Yes its backwards, look at the USPS api for why
        $this->_addressLineTwo = isset($address['Address1']) ? $address['Address1'] : null;
        $this->_city = $address['City'];
        $this->_state = $address['State'];
        $postal4 = isset($address['Zip4']) ? '-'.$address['Zip4'] : null;
        $this->_postalCode = $address['Zip5'] . $postal4;
        $this->_verifiedAddress = true;

        $this->update();

        return true;
    }

    //Setters
    public function setRecipientId($recipientId){$this->_recipientId = $recipientId; return $this;}
    public function setBatchId($batchId){$this->_batchId = $batchId; return $this;}
    public function setEmail($email){$this->_email = $email; return $this;}
    public function setFirstName($firstName){$this->_firstName = $firstName; return $this;}
    public function setLastName($lastName){$this->_lastName = $lastName; return $this;}
    public function setAddressLineOne($addressLineOne){$this->_addressLineOne = $addressLineOne; return $this;}
    public function setAddressLineTwo($addressLineTwo){$this->_addressLineTwo = $addressLineTwo; return $this;}
    public function setCity($city){$this->_city = $city; return $this;}
    public function setState($state){$this->_state = $state; return $this;}
    public function setPostalCode($postalCode){$this->_postalCode = $postalCode; return $this;}
    public function setVerifiedAddress($verifiedAddress){$this->_verifiedAddress = $verifiedAddress; return $this;}
    public function setShipTs($shipTs){$this->_shipTs = $shipTs; return $this;}
    public function setTrackingNumber($trackingNumber){$this->_trackingNumber = $trackingNumber; return $this;}
    public function setShirtSex($shirtSex){$this->_shirtSex = $shirtSex; return $this;}
    public function setShirtSize($shirtSize){$this->_shirtSize = $shirtSize; return $this;}
    public function setShirtType($shirtType){$this->_shirtType = $shirtType; return $this;}
    public function setQuantity($quantity){$this->_quantity = $quantity; return $this;}

    //Getters
    public function getRecipientId(){return $this->_recipientId;}
    public function getBatchId(){return $this->_batchId;}
    public function getEmail(){return $this->_email;}
    public function getFirstName(){return $this->_firstName;}
    public function getLastName(){return $this->_lastName;}
    public function getAddressLineOne(){return $this->_addressLineOne;}
    public function getAddressLineTwo(){return $this->_addressLineTwo;}
    public function getCity(){return $this->_city;}
    public function getState(){return $this->_state;}
    public function getPostalCode(){return $this->_postalCode;}
    public function getVerifiedAddress(){return $this->convertFromBoolean($this->_verifiedAddress);}
    public function getInsertTs(){return $this->_insertTs;}
    public function getShipTs(){return $this->_shipTs;}
    public function getTrackingNumber(){return $this->_trackingNumber;}
    public function getShirtSex(){return $this->_shirtSex;}
    public function getShirtSize(){return $this->_shirtSize;}
    public function getShirtType(){return $this->_shirtType;}
    public function getQuantity(){return $this->_quantity;}
    public function getTotal(){return $this->_total;}
}