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

    public function massInsertFromCsv($filePath, $batchId)
    {
        if(empty($batchId) || !is_numeric($batchId)) {
            throw new Zend_Exception('You must provide a batch id');
        }
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
            )");

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
}