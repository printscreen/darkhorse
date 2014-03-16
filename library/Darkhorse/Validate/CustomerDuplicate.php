<?php
class Darkhorse_Validate_CustomerDuplicate extends Zend_Validate_Abstract
{
    const IN_USE = 'inuse';
    protected $_token;
    protected $_customerId;

    protected $_messageTemplates = array(
        self::IN_USE => "'%value%' is already in use by another customer"
    );

    public function __construct($token, $customerId = 'customerId')
    {
        $this->_token = $token;
        $this->_customerId = $customerId;
    }

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
        $name = isset($context[$this->_token]) ? $context[$this->_token] : null;
        $customerId = isset($context[$this->_customerId]) ? $context[$this->_customerId] : null;
        if(empty($name)) {
            return false;
        }

        $customer = new Model_Customer(array('name'=>$name));
        $customer->load();
        $foundId = $customer->getCustomerId();

        if(is_numeric($foundId) && $foundId != $customerId) {
            $this->_error(self::IN_USE);
            return false;
        }
        return true;
    }
}
