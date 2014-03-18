<?php
class Form_Batch extends Darkhorse_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $batchId = new Zend_Form_Element_Hidden('batchId');
        $batchId->setRequired(false)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits');
        $this->addElement($batchId);

        $customerId = new Zend_Form_Element_Hidden('customerId');
        $customerId->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits');
        $this->addElement($customerId);

        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty', true);
        $this->addElement($name);

        $contactName = new Zend_Form_Element_Text('contactName');
        $contactName->setRequired(false)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true);
        $this->addElement($contactName);

        $contactPhoneNumber = new Zend_Form_Element_Text('contactPhoneNumber');
        $contactPhoneNumber->setRequired(false)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true);
        $this->addElement($contactPhoneNumber);

        $contactEmail = new Zend_Form_Element_Text('contactEmail');
        $contactEmail->setRequired(false)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true)
               ->addValidator('EmailAddress', true);
        $this->addElement($contactEmail);

        $street = new Zend_Form_Element_Text('street');
        $street->setRequired(false)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true);
        $this->addElement($street);

        $suiteApt = new Zend_Form_Element_Text('suiteApt');
        $suiteApt->setRequired(false)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true);
        $this->addElement($suiteApt);

        $city = new Zend_Form_Element_Text('city');
        $city->setRequired(false)
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addValidator('NotEmpty', true);
        $this->addElement($city);

        $state = new Darkhorse_Form_Element_StateSelect('state');
        $state->setRequired(false)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty', true);
        $this->addElement($state);

        $postalCode = new Zend_Form_Element_Text('postalCode');
        $postalCode->setRequired(false)
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('NotEmpty', true);
        $this->addElement($postalCode);

        $active = new Zend_Form_Element_Select('active');
        $active->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->setMultiOptions(array('true'=>'Yes', 'false'=>'No'))
                 ->addValidator('NotEmpty', true);
        $this->addElement($active);
    }
}