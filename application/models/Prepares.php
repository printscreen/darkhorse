<?php
class Model_Prepares extends Model_Base_Db
{
    protected $_prepares;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);

        $this->_prepares = array();
    }

    public function getProducts($batchId)
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
                    batch_id
                  , shirt_sex
                  , shirt_size
                  , shirt_type
                  , weight
                FROM recipient
                WHERE batch_id = :batchId
                GROUP BY
                    shirt_sex
                  , shirt_size
                  , shirt_type
                  , weight;
        ";

        $query = $this->_db->prepare($sql);
        $this->bind($query, $binds);
        $query->execute();

        $this->_prepares = array();

        while($result = $query->fetch()) {
            $prepare = new Model_Prepare();
            $prepare->loadRecord($result);
            $this->_prepares[] = $prepare;
        }

        return $this->_prepares;
    }

    public function toArray()
    {
        $prepares = array();
        if(is_array($this->_prepares) && count($this->_prepares) > 0) {
            foreach($this->_prepares as $prepare) {
                $prepares[] = $prepare->toArray();
            }
        }
        return $prepares;
    }
}