<?php
class Darkhorse_Validate_ScanFromRecipients extends Zend_Validate_Abstract
{
    const BAD_RECIPIENT = 'bad_recipient';
    protected $_token;
    protected $_recipientIds;

    protected $_messageTemplates = array(
        self::BAD_RECIPIENT => "Some recipients can not be added to scan form"
    );

    public function __construct($token, $recipientIds = 'recipientIds')
    {
        $this->_token = $token;
        $this->_recipientIds= $recipientIds;
    }

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
        if(empty($value) || !is_array($value)) {
            return false;
        }

        $scanFrom = new Model_ScanFrom();
        $recipients = $scanFrom->findBadRecipients($value);

        if(is_array($recipients) && !empty($recipients)) {
            $this->_error(self::BAD_RECIPIENT);
            return false;
        }
        return true;
    }
}
