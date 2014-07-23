<?php
class Form_ScanForm extends Darkhorse_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $scanFormId = new Zend_Form_Element_Hidden('scanFormId');
        $scanFormId->setRequired(false)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits')
              ->addValidator(new Darkhorse_Validate_ScanFormLocked(), true);
        $this->addElement($scanFormId);

        $batchId = new Zend_Form_Element_Hidden('batchId');
        $batchId->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits');
        $this->addElement($batchId);

        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true)
               ->addValidator(new Darkhorse_Validate_ScanFormDuplicate('name'), true)
               ->setAttrib('placeholder', 'Name');
        $this->addElement($name);
    }
}