<?php
class Form_ProductWeight extends Darkhorse_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $batchId = new Zend_Form_Element_Hidden('batchId');
        $batchId->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits');
        $this->addElement($batchId);

        $shirtSex = new Zend_Form_Element_Text('shirtSex');
        $shirtSex->setRequired(true)
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('NotEmpty', true);
        $this->addElement($shirtSex);

        $shirtSize = new Zend_Form_Element_Text('shirtSize');
        $shirtSize->setRequired(true)
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('NotEmpty', true);
        $this->addElement($shirtSize);

        $shirtType = new Zend_Form_Element_Text('shirtType');
        $shirtType->setRequired(true)
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->addValidator('NotEmpty', true);
        $this->addElement($shirtType);

        $weight = new Zend_Form_Element_Text('weight');
        $weight->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits');
        $this->addElement($weight);
    }
}