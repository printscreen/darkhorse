<?php
class Darkhorse_Validate_EmailDuplicate extends Zend_Validate_Abstract
{
    const IN_USE = 'inuse';
    protected $_token;
    protected $_userId;

    protected $_messageTemplates = array(
        self::IN_USE => "'%value%' is already in use by another user"
    );

    public function __construct($token, $userId = 'userId')
    {
        $this->_token = $token;
        $this->_userId = $userId;
    }

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
        $emailValidate = new Zend_Validate_EmailAddress();
        $email = isset($context[$this->_token]) && $emailValidate->isValid($context[$this->_token]) ? $context[$this->_token] : null;
        $userId = isset($context[$this->_userId]) ? $context[$this->_userId] : null;
        if(empty($email)) {
            return false;
        }

        $user = new Model_User(array('email'=>$email));
        $user->load();
        $foundId = $user->getUserId();

        if(is_numeric($foundId) && $foundId != $userId) {
            $this->_error(self::IN_USE);
            return false;
        }
        return true;
    }
}
