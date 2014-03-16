<?php
class Model_UserTypeResources extends Model_Base_Db
{
    protected $_userTypeResources;
    protected $_userTypeId;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'userTypeId' => null,
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);

        $this->_userTypeId = $settings['userTypeId'];
        $this->_userTypeResources = array();
    }

    public function getUserTypeResources()
    {
        $sql = 'SELECT
                    utr.user_type_resource_id   AS user_type_resource_id,
                    utr.user_type_id            AS user_type_id,
                    ut.name                     AS user_type_name,
                    utr.resource_id             AS resource_id,
                    r.name                      AS resource_name
                FROM user_type_resource utr
                INNER JOIN user_type ut ON utr.user_type_id = ut.user_type_id
                INNER JOIN resource r ON utr.resource_id = r.resource_id
                WHERE utr.user_type_id = :userTypeId
        ';
        $query = $this->_db->prepare($sql);

        $userTypeId = $this->convertToInt($this->_userTypeId);
        $query->bindParam(':userTypeId', $userTypeId, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetchAll();

        $this->_userTypeResources = array();
        if(!empty($result)) {
            foreach($result as $key => $value) {
                $typeResource = new Model_UserTypeResource();
                $typeResource->loadRecord($value);
                $this->_userTypeResources[] = $typeResource;
            }
        }
        return $this->_userTypeResources;
    }
}