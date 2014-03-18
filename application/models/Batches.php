<?php
class Model_Batches extends Model_Base_Db
{
    protected $_batches;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);

        $this->_batches = array();
    }

    public function getBatches($customerId, $active = true, $sort = null, $offset = null, $limit = null)
    {
        $where = '';
        $binds = array();
        $active = $this->convertFromBoolean($active);
        if(!empty($customerId) && is_numeric($customerId)) {
            $where .= ' AND b.customer_id = :customerId';
            $binds[':customerId'] = array('value' => $customerId, 'type' => PDO::PARAM_INT);
        }
        if (!is_null($active)) {
            $where .= ' AND b.active = :active';
            $binds[':active'] = array('value' => $active, 'type' => PDO::PARAM_BOOL);
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
              , ( SELECT
                    count(*)
                  FROM batch b
                  INNER JOIN customer c ON b.customer_id = c.customer_id
                  WHERE true
                  $where
                ) AS total
            FROM batch b
            INNER JOIN customer c ON b.customer_id = c.customer_id
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

        $result = $query->fetchAll();

        $this->_batches = array();
        if(!empty($result)) {
            foreach($result as $key => $value) {
                $batch = new Model_Batch();
                $batch->loadRecord($value);
                $this->_batches[] = $batch;
            }
        }
        return $this->_batches;
    }

    public function toArray()
    {
        $batches = array();
        if(is_array($this->_batches) && count($this->_batches) > 0) {
            foreach($this->_batches as $batch) {
                $batches[] = $batch->toArray();
            }
        }
        return $batches;
    }
}