<?php
class Form_Recipient extends Darkhorse_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $recipientId = new Zend_Form_Element_Hidden('recipientId');
        $recipientId->setRequired(false)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits');
        $this->addElement($recipientId);

        $batchId = new Zend_Form_Element_Hidden('batchId');
        $batchId->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits');
        $this->addElement($batchId);

        $email = new Zend_Form_Element_Text('email');
        $email->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true)
               ->addValidator('EmailAddress', true)
               ->setAttrib('placeholder', 'Email');
        $this->addElement($email);

        $firstName = new Zend_Form_Element_Text('firstName');
        $firstName->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty', true);
        $this->addElement($firstName);

        $lastName = new Zend_Form_Element_Text('lastName');
        $lastName->setRequired(false)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true);
        $this->addElement($lastName);

        $addressLineOne = new Zend_Form_Element_Text('addressLineOne');
        $addressLineOne->setRequired(false)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true);
        $this->addElement($addressLineOne);

        $addressLineTwo = new Zend_Form_Element_Text('addressLineTwo');
        $addressLineTwo->setRequired(false)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty', true);
        $this->addElement($addressLineTwo);

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

        $shirtSex = new Zend_Form_Element_Text('shirtSex');
        $shirtSex->setRequired(false)
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('NotEmpty', true);
        $this->addElement($shirtSex);

        $shirtSize = new Zend_Form_Element_Text('shirtSize');
        $shirtSize->setRequired(false)
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('NotEmpty', true);
        $this->addElement($shirtSize);

        $shirtType = new Zend_Form_Element_Text('shirtType');
        $shirtType->setRequired(false)
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('NotEmpty', true);
        $this->addElement($shirtType);

        $quantity = new Zend_Form_Element_Text('quantity');
        $quantity->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits');
        $this->addElement($quantity);

    }
}