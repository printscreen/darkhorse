<?php
class Model_ScanForms extends Model_Base_Db
{
    protected $_scanForms;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);

        $this->_scanFroms = array();
    }

    public function getScanForms($batchId, $sort = null, $offset = null, $limit = null)
    {
        $where = '';
        $binds = array();
        if(!empty($batchId) && is_numeric($batchId)) {
            $where .= ' AND sf.batch_id = :batchId';
            $binds[':batchId'] = array('value' => $batchId, 'type' => PDO::PARAM_INT);
        } else {
            throw new Zend_Exception('No batch id supplied');
        }

        $sql = "SELECT
                sf.scan_form_id
              , sf.batch_id
              , sf.name
              , sf.insert_ts
              , sf.is_generated
              , ( SELECT
                    count(*)
                  FROM scan_form sf
                  WHERE true
                  $where
                ) AS total
            FROM scan_form sf
            WHERE true
            $where
            ORDER BY :sort " . $this->getDirection($sort) . "
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

        $this->_scanForms = array();

        while($result = $query->fetch()) {
            $scanForm = new Model_ScanForm();
            $scanForm->loadRecord($result);
            $this->_scanForms[] = $scanForm;
        }

        return $this->_scanFroms;
    }
}