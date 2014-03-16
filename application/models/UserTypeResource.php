<?php
class Model_UserTypeResource extends Model_Base_Db
{
    protected $_userTypeResourceId;
    protected $_userTypeId;
    protected $_userTypeName;
    protected $_resourceId;
    protected $_resourceName;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'userTypeResourceId' => null,
            'userTypeId' => null,
            'userTypeName' => null,
            'resourceId' => null,
            'resourceName' => null,
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);
        $this->_userTypeResourceId = $settings['userTypeResourceId'];
        $this->_userTypeId = $settings['userTypeId'];
        $this->_userTypeName = $settings['userTypeName'];
        $this->_resourceId = $settings['resourceId'];
        $this->_resourceName = $settings['resourceName'];
    }
    public function loadRecord($typeRecord)
    {
        $this->_userTypeResourceId = $typeRecord->user_type_resource_id;
        $this->_userTypeId = $typeRecord->user_type_id;
        $this->_userTypeName = $typeRecord->user_type_name;
        $this->_resourceId = $typeRecord->resource_id;
        $this->_resourceName = $typeRecord->resource_name;
    }

    //Setters
    public function setUserTypeResourceId($userTypeResourceId){$this->_userTypeResourceId = $userTypeResourceId;}
    public function setUserTypeId($userTypeId){$this->_userTypeId = $userTypeId;}
    public function setUserTypeName($userTypeName){$this->_userTypeName = $userTypeName;}
    public function setResourceId($resourceId){$this->_resourceId = $resourceId;}
    public function setResourceName($resourceName){$this->_resourceName = $resourceName;}

    //Getters
    public function getUserTypeResourceId(){return $this->_userTypeResourceId;}
    public function getUserTypeId(){return $this->_userTypeId;}
    public function getUserTypeName(){return $this->_userTypeName;}
    public function getResourceId(){return $this->_resourceId;}
    public function getResourceName(){return $this->_resourceName;}
}