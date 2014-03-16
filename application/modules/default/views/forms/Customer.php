<?php
class Form_Customer extends Darkhorse_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $customerId = new Zend_Form_Element_Hidden('customerId');
        $customerId->setRequired(false)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits')
              ->addErrorMessage('Not a valid customer id');
        $this->addElement($customerId);

        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty', true)
                  ->addValidator(new Darkhorse_Validate_CustomerDuplicate('name'), true)
                  ->setAttrib('placeholder', 'Name');
        $this->addElement($name);

        $active = new Zend_Form_Element_Select('active');
        $active->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addErrorMessage('Please enter active')
                 ->setMultiOptions(array('true'=>'Yes', 'false'=>'No'));
        $this->addElement($active);
    }
}