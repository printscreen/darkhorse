<?php
class Darkhorse_Validate_ScanFormLocked extends Zend_Validate_Abstract
{
    const SCANFORM_LOCKED = 'scanform_locked';
    protected $_token;

    protected $_messageTemplates = array(
        self::SCANFORM_LOCKED => "Scan from is generated and can not be modified"
    );

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
        if(empty($value) || !is_numeric($value)) {
            return true;
        }

        $scanForm = new Model_ScanForm(array(
            'scanFormId' => $value
        ));
        $scanForm->load();

        if($scanForm->isGenerated()) {
            $this->_error(self::SCANFORM_LOCKED);
            return false;
        }
        return true;
    }
}
