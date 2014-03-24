<?php
class Form_StageBatch extends Darkhorse_Form
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

        $file = new Zend_Form_Element_File('file');
        $file->setRequired(true)
              ->addValidator(new Zend_Validate_File_Extension(array('csv')));
        $this->addElement($file);
    }
}