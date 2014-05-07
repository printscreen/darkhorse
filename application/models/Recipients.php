<?php
class Model_Recipients extends Model_Base_Db
{
    protected $_recipients;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);

        $this->_recipients = array();
    }

    public function getRecipients($batchId, $searchField = null, $searchText = null, $sort = null, $offset = null, $limit = null)
    {
        $where = '';
        $binds = array();
        if(!empty($batchId) && is_numeric($batchId)) {
            $where .= ' AND r.batch_id = :batchId';
            $binds[':batchId'] = array('value' => $batchId, 'type' => PDO::PARAM_INT);
        } else {
            throw new Zend_Exception('No batch id supplied');
        }
        $field = trim($searchField);
        $text = trim($searchText);
        if(!empty($field) && !empty($text)) {
            $where .= ' AND r.'.$field.' LIKE :'.$field;
            $binds[':'.$field] = array('value' => $text.'%', 'type' => PDO::PARAM_STR);
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
              , ( SELECT
                    count(*)
                  FROM recipient r
                  WHERE true
                  $where
                ) AS total
            FROM recipient r
            WHERE true
            $where
            ORDER BY :sort
            LIMIT :offset,:limit
        ";

        $query = $this->_db->prepare($sql);

        $sort = $this->getSort($sort);
        $offset = $this->getOffset($offset);
        $limit = $this->getLimit($limit);
        $query->bindParam(':sort', $sort, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $this->bind($query, $binds);
        $query->execute();

        $this->_recipients = array();

        while($result = $query->fetch()) {
            $recipient = new Model_Recipient();
            $recipient->loadRecord($result);
            $this->_recipients[] = $recipient;
        }

        return $this->_recipients;
    }

    public function verifyRecipients($batchId, $status)
    {
        $where = '';
        $binds = array();
        if(!empty($batchId) && is_numeric($batchId)) {
            $where .= ' AND r.batch_id = :batchId';
            $binds[':batchId'] = array('value' => $batchId, 'type' => PDO::PARAM_INT);
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
              , ( SELECT
                    count(*)
                  FROM recipient r
                  WHERE true
                  $where
                ) AS total
            FROM recipient r
            WHERE NOT verified_address
            $where
        ";

        $query = $this->_db->prepare($sql);
        $this->bind($query, $binds);
        $query->execute();

        $count = 0;
        $cache = Zend_Registry::get(CACHE);
        while($result = $query->fetch()) {
            $count++;
            $recipient = new Model_Recipient();
            $recipient->loadRecord($result);
            $total = $recipient->getTotal();
            $recipient->verify();
            if($status) {
                $cache->save("$count|$total", $status);
            }
        }
    }

    public function massInsertFromCsv($filePath, $batchId)
    {
        if(empty($batchId) || !is_numeric($batchId)) {
            throw new Zend_Exception('You must provide a batch id');
        }

        // Remove mac line endings
        $lines = file_get_contents($filePath);
        $lines = str_replace("\r\n", "\n", $lines);
        $lines = str_replace("\r", "\n", $lines);
        file_put_contents($filePath, $lines);

        // Need to tack on batch id to the CSV before import
        $fh = fopen($filePath, 'r');
        $insertFile = tmpfile();
        $fileInfo = stream_get_meta_data($insertFile);
        $insertFilePath = $fileInfo["uri"];
        while ($data = fgetcsv($fh, 1000, ',')) {
            fputcsv($insertFile, array_merge(
                  array($batchId)
                , array_map('trim', $data)
            ));
        }

        $query = $this->_db->prepare("
            LOAD DATA LOCAL INFILE '$insertFilePath'
            INTO TABLE recipient
            FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
            LINES TERMINATED BY '\n'
            IGNORE 1 LINES (
                batch_id
              , email
              , first_name
              , last_name
              , address_line_one
              , address_line_two
              , city
              , state
              , postal_code
              , shirt_sex
              , shirt_size
              , shirt_type
              , quantity
            )
            SET
              email = TRIM(email),
              first_name = TRIM(first_name),
              last_name = TRIM(last_name),
              address_line_one = TRIM(address_line_one),
              address_line_two = TRIM(address_line_two),
              city = TRIM(city),
              state = TRIM(state),
              postal_code = TRIM(postal_code),
              shirt_sex = UPPER(TRIM(shirt_sex)),
              shirt_size = UPPER(TRIM(shirt_size)),
              shirt_type = UPPER(TRIM(shirt_type))");

        $result = $query->execute();

        if(!$result) {
            return false;
        }

        return true;
    }

    public function moveRecipients($fromBatchId, $toBatchId, $who, $recipientIds = array())
    {
        $where = 'WHERE r.ship_ts IS NULL AND r.batch_id = :fromBatchId';
        $binds[':fromBatchId'] = array('value' => $fromBatchId, 'type' => PDO::PARAM_INT);
        $binds[':toBatchId'] = array('value' => $toBatchId, 'type' => PDO::PARAM_INT);

        if($who !== 'all' && empty($recipientIds)) {
            throw new Zend_Exception('No recipient ids supplied');
        } elseif($who !== 'all' && count($recipientIds) > 0) {
            $ids = array();
            foreach($recipientIds as $key => $id) {
                $ids[] = ':id_'.$id;
                $binds[':id_'.$id] = array('value' => $id, 'type' => PDO::PARAM_INT);
            }
            $where .= ' AND r.recipient_id IN ('. implode(',', $ids) .')';
        }
        $sql = "UPDATE recipient r
                SET r.batch_id = :toBatchId
                $where
        ";

        $query = $this->_db->prepare($sql);
        $this->bind($query, $binds);

        $result = $query->execute();

        if(!$result) {
            return false;
        }

        return true;
    }

    public function toArray()
    {
        $recipients = array();
        if(is_array($this->_recipients) && count($this->_recipients) > 0) {
            foreach($this->_recipients as $recipient) {
                $recipients[] = $recipient->toArray();
            }
        }
        return $recipients;
    }

    public function export($filePointer, $batchId)
    {
        $where = '';
        $binds = array();
        if(!empty($batchId) && is_numeric($batchId)) {
            $where .= ' AND r.batch_id = :batchId';
            $binds[':batchId'] = array('value' => $batchId, 'type' => PDO::PARAM_INT);
        } else {
            throw new Zend_Exception('No batch id supplied');
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
              , ( SELECT
                    count(*)
                  FROM recipient r
                  WHERE true
                  $where
                ) AS total
            FROM recipient r
            WHERE true
            $where
            ORDER BY 1
            LIMIT 0,".PHP_INT_MAX;

        $query = $this->_db->prepare($sql);

        $this->bind($query, $binds);
        $query->execute();

        fputcsv($filePointer, array(
            'Email'
          , 'First Name'
          , 'Last Name'
          , 'Address Line One'
          , 'Address Line Two'
          , 'City'
          , 'State'
          , 'Postal Code'
          , 'Shirt Sex'
          , 'Shirt Size'
          , 'Shirt Type'
          , 'Quantity'
        ));

        while($result = $query->fetch()) {
            $recipient = new Model_Recipient();
            $recipient->loadRecord($result);
            fputcsv($filePointer, array(
                $recipient->getEmail()
              , $recipient->getFirstName()
              , $recipient->getLastName()
              , $recipient->getAddressLineOne()
              , $recipient->getAddressLineTwo()
              , $recipient->getCity()
              , $recipient->getState()
              , $recipient->getPostalCode()
              , $recipient->getShirtSex()
              , $recipient->getShirtSize()
              , $recipient->getShirtType()
              , $recipient->getQuantity()
            ));
        }
    }
}