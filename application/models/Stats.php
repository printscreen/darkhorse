<?php
class Model_Stats extends Model_Base_Db
{
    protected $_stats;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);

        $this->_stats = array();
    }

    public function getStats($batchId)
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
                    shirt_sex
                  , shirt_size
                  , shirt_type
                  , count(*)
                FROM recipient
                WHERE batch_id = :batchId
                GROUP BY
                    shirt_sex
                  , shirt_size
                  , shirt_type;
        ";

        $query = $this->_db->prepare($sql);
        $this->bind($query, $binds);
        $query->execute();

        $this->_stats = array();

        while($result = $query->fetch()) {
            $stat = new Model_Stat();
            $stat->loadRecord($result);
            $this->_stats[] = $stat;
        }

        return $this->_stats;
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
                    shirt_sex
                  , shirt_size
                  , shirt_type
                  , count(*) AS quantity
                FROM recipient
                WHERE batch_id = :batchId
                GROUP BY
                    shirt_sex
                  , shirt_size
                  , shirt_type;
        ";

        $query = $this->_db->prepare($sql);
        $this->bind($query, $binds);
        $query->execute();

        fputcsv($filePointer, array(
            'Shirt Sex'
          , 'Shirt Size'
          , 'Shirt Type'
          , 'Quantity'
        ));

        while($result = $query->fetch()) {
            $stat = new Model_Stat();
            $stat->loadRecord($result);
            fputcsv($filePointer, array(
                $stat->getShirtSex()
              , $stat->getShirtSize()
              , $stat->getShirtType()
              , $stat->getQuantity()
            ));
        }
    }

    public function toArray()
    {
        $stats = array();
        if(is_array($this->_stats) && count($this->_stats) > 0) {
            foreach($this->_stats as $stat) {
                $stats[] = $stat->toArray();
            }
        }
        return $stats;
    }
}