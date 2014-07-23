<?php
class Model_ScanForm extends Model_Base_Db
{
    protected $_scanFormId;
    protected $_batchId;
    protected $_name;
    protected $_insertTs;
    protected $_isGenerated;
    protected $_total;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'scanFormId' => null,
            'batchId' => null,
            'name' => null,
            'insertTs' => null,
            'isGenerated' => null,
            'db' => null,
            ), $options);
        parent::__construct($settings['db']);
        $this->_scanFormId = $settings['scanFormId'];
        $this->_batchId = $settings['batchId'];
        $this->_name = $settings['name'];
        $this->_insertTs = $settings['insertTs'];
        $this->_isGenerated = $settings['isGenerated'];
    }

    public function loadRecord($record)
    {
        $this->_scanFormId = $record->scan_form_id;
        $this->_batchId = $record->batch_id;
        $this->_name = $record->name;
        $this->_insertTs = $record->insert_ts;
        $this->_isGenerated = $record->is_generated;
        $this->_total = $record->total;
    }

    public function load()
    {
        $where = 'WHERE true';
        $binds = array();
        if(is_numeric($this->_scanFormId)) {
            $where .= ' AND sf.scan_form_id = :scanFormId';
            $binds[':scanFormId'] = array('value' => $this->_scanFormId, 'type' => PDO::PARAM_INT);
        } else if(is_numeric($this->_batchId) && !empty($this->_name)) {
            $where .= ' AND sf.batch_id = :batchId AND name = :name';
            $binds[':batchId'] = array('value' => $this->_batchId, 'type' => PDO::PARAM_INT);
            $binds[':name'] = array('value' => $this->_name, 'type' => PDO::PARAM_STR);
        } else {
            throw new Zend_Exception("No scan form id supplied");
        }

        $sql = "SELECT
                sf.scan_form_id
              , sf.batch_id
              , sf.name
              , sf.insert_ts
              , sf.is_generated
              , 1 AS total
            FROM scan_form sf
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
        $sql = "INSERT INTO scan_form (
                    batch_id
                  , name
                  , insert_ts
                  , is_generated
                )
                VALUES (
                    :batchId
                  , UPPER(:name)
                  , CURRENT_TIMESTAMP
                  , :isGenerated
                )";

        $query = $this->_db->prepare($sql);

        $batchId = $this->convertToInt($this->_batchId);
        $isGenerated = $this->convertFromBoolean($this->_isGenerated);
        $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $query->bindParam(':name', $this->_name, PDO::PARAM_STR);
        $query->bindParam(':isGenerated', $isGenerated, PDO::PARAM_BOOL);
        $result = $query->execute();

        if(!$result) {
            return false;
        }
        $this->_scanFormId = $this->_db->lastInsertId('scan_form','scan_form_id');

        return true;
    }

    public function update()
    {
        if(empty($this->_scanFormId) || !is_numeric($this->_scanFormId)) {
            throw new Zend_Exception('No scan form id supplied');
        }
        $sql = "UPDATE scan_form SET
                    batch_id = COALESCE(:batchId, batch_id)
                  , name = COALESCE(UPPER(:name), name)
                  , is_generated = COALESCE(:isGenerated, is_generated)
                  WHERE scan_form_id = :scanFormId;
                ";

        $query = $this->_db->prepare($sql);

        $batchId = $this->convertToInt($this->_batchId);
        $isGenerated = $this->convertFromBoolean($this->_isGenerated);
        $scanFormId = $this->convertToInt($this->_scanFormId);
        $query->bindParam(':batchId', $batchId, PDO::PARAM_INT);
        $query->bindParam(':name', $this->_name, PDO::PARAM_STR);
        $query->bindParam(':isGenerated', $isGenerated, PDO::PARAM_BOOL);
        $query->bindParam(':scanFormId', $scanFormId, PDO::PARAM_INT);
        $result = $query->execute();

        if(!$result) {
            return false;
        }
        return true;
    }

    public function generateScanForm()
    {
        if(!$this->load()) {
            throw new Zend_Exception('Unable to find scan form');
        }

        $path = $this->getFilePath();
        if(file_exists("$path/$this->_scanFormId.gif")) {
            return true;
        }

        $label = new Darkhorse_Endicia_Client(array(
            'requesterId' => Zend_Registry::get(ENDICIA_REQUESTER_ID)
          , 'accountId' => Zend_Registry::get(ENDICIA_ACCOUNT_ID)
          , 'passPhrase' => Zend_Registry::get(ENDICIA_PASSPHRASE)
          , 'isTestEnv' => 'production' != APPLICATION_ENV
        ));

        $sql = "SELECT
                    tracking_number
                FROM recipient
                WHERE scan_form_id = :scanFormId
                AND tracking_number IS NOT NULL";

        $query = $this->_db->prepare($sql);

        $scanFormId = $this->convertToInt($this->_scanFormId);
        $query->bindParam(':scanFormId', $scanFormId, PDO::PARAM_INT);
        $query->execute();

        $trackingNumbers = array();
        while($result = $query->fetch()) {
            array_push($trackingNumbers, $result->tracking_number);
        }

        if(empty($trackingNumbers)) {
            throw new Zend_Exception('Unable to create scan form with 0 tracking numbers');
        }

        $batch = new Model_Batch(array(
            'batchId' => $this->_batchId
        ));
        $batch->load();

        try {
            $response = $label->getScanForm(array(
                'submissionId' => $this->_scanFormId
              , 'fromName' => $batch->getName()
              , 'fromAddress' => trim($batch->getStreet() . ' ' .$batch->getSuiteApt())
              , 'fromCity' => $batch->getCity()
              , 'fromState' => $batch->getState()
              , 'fromZip' => $batch->getZip()
              , 'fromPhone' => null
              , 'trackingNumbers' => $trackingNumbers
            ));
var_dump($response); die;
            $image = base64_decode($response->SCANForm);
            $this->_isGenerated = true;
            self::setFile($image);
            self::update();

        } catch(Zend_Exception $ze) {
            return false;
        }
        return true;
    }

    private function setFile($file)
    {
        if(empty($this->_insertTs) || !is_numeric($this->_scanFormId)) {
            throw new Zend_Exception('Unable to create directory from null insertTs or id');
        }

        $path = $this->getFilePath();
        if(!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        file_put_contents("$path/$this->_scanFormId.gif", $file);
    }

    public function getScanForm()
    {
        $path = $this->getFilePath();
        if(!file_exists("$path/$this->_scanFormId.gif")) {
            throw new Zend_Exception('Unable to locate file');
        }
        return file_get_contents($filePath);
    }

    private function getFilePath()
    {
        $filePath = sprintf('%s/scan_forms/%s/'
          , Zend_Registry::get(STAMPS_FILE_PATH)
          , date('Y', strtotime($this->_insertTs))
        );
        return $filePath;
    }

    /**
     * Pass an array of recipients ids and check to make sure
     * each recipient can be add to a scan form.
     *
     * @param array $recipients Array of recipientIds
     * @return array $recipientIds Recipients who already have been associated to a scan form
     */
    public function findBadRecipients(array $recipients = array())
    {
        $sql = "SELECT
                    recipient_id
                FROM
                    recipient
                WHERE scan_form_id IS NOT NULL
                AND recipient_id IN (". $this->arrayToIn($recipients).")";
        $query = $this->_db->prepare($sql);

        foreach($recipients as $recipient) {
            $recipientId = $this->convertToInt($recipient);
            $query->bindParam(":$recipient", $recipientId, PDO::PARAM_INT);
        }

        $query->execute();

        return $query->fetchAll();
    }

    public function isGenerated()
    {
        return (bool)$this->_isGenerated;
    }

    //Setters
    public function setScanFormId($scanFormId){$this->_scanFormId = $scanFormId; return $this;}
    public function setBatchId($batchId){$this->_batchId = $batchId; return $this;}
    public function setName($name){$this->_name = $name; return $this;}
    public function setIsGenerated($isGenerated){$this->_isGenerated = $isGenerated; return $this;}

    //Getters
    public function getScanFormId(){return $this->_scanFormId;}
    public function getBatchId(){return $this->_batchId;}
    public function getName(){return $this->_name;}
    public function getInsertTs(){return $this->_insertTs;}
    public function getIsGenerated(){return $this->_isGenerated;}
    public function getTotal(){return $this->_total;}
}