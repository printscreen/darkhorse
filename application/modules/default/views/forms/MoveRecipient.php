<?php
class Form_MoveRecipient extends Darkhorse_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $fromBatchId = new Zend_Form_Element_Hidden('fromBatchId');
        $fromBatchId->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits')
              ->addErrorMessage('Not a valid batch id');
        $this->addElement($fromBatchId);

        $toBatchId = new Zend_Form_Element_Hidden('toBatchId');
        $toBatchId->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits')
              ->addErrorMessage('Not a valid batch id');
        $this->addElement($toBatchId);

        $who = new Zend_Form_Element_Select('who');
        $who->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addErrorMessage('Please enter a selection')
                 ->setMultiOptions(array('selected'=>'Move only selected recipients', 'all'=>'All unshipped recipients'));
        $this->addElement($who);

        $recipientIds = new Zend_Form_Element_Hidden('recipientIds');
        $recipientIds->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty',true)
              ->addValidator('Digits')
              ->setIsArray(true);
        $this->addElement($recipientIds);
    }
}