<?php
class Darkhorse_Validate_ScanFormDuplicate extends Zend_Validate_Abstract
{
    const IN_USE = 'inuse';
    protected $_token;
    protected $_scanFormId;
    protected $_batchId;

    protected $_messageTemplates = array(
        self::IN_USE => "'%value%' is already in use"
    );

    public function __construct($token, $scanFormId = 'scanFormId', $batchId = 'batchId')
    {
        $this->_token = $token;
        $this->_scanFormId = $scanFormId;
        $this->_batchId = $batchId;
    }

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
        $name = isset($context[$this->_token]) ? $context[$this->_token] : null;
        $scanFormId = isset($context[$this->_scanFormId]) ? $context[$this->_scanFormId] : null;
        $batchId = isset($context[$this->_batchId]) ? $context[$this->_batchId] : null;
        if(empty($name)) {
            return false;
        }

        $scanForm = new Model_ScanForm(array(
            'batchId' => $batchId
          , 'name'=>strtoupper(trim($name))
        ));
        $scanForm->load();
        $foundId = $scanForm->getScanFormId();

        if(is_numeric($foundId) && $foundId != $scanFormId) {
            $this->_error(self::IN_USE);
            return false;
        }
        return true;
    }
}
