<?php
class Darkhorse_Form extends Zend_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    public function getFormErrors()
    {
        $errors = array();
        if(is_array($this->_elements) && !empty($this->_elements)) {
            foreach($this->_elements as $key => $val) {
                if($val->hasErrors()) {
                    $errors[$key] = current($val->getMessages());
                }
            }
        }
        return $errors;
    }
}