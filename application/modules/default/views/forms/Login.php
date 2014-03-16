<?php
class Form_Login extends Darkhorse_Form
{
    public function __construct($options = null)
    {
        parent::__construct($options);

        $email = new Zend_Form_Element_Text('email');
        $email->setRequired(true)
            ->addValidator('NotEmpty', true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addFilter('StringToLower')
            ->addValidator('NotEmpty',true)
            ->addValidator('EmailAddress')
            ->addErrorMessage('Must be a valid email address')
            ->setAttrib('class', 'form-control')
            ->setAttrib('placeholder', 'Email address')
            ->removeDecorator('HtmlTag')
            ->removeDecorator('Label');
        $this->addElement($email);

        $password = new Zend_Form_Element_Password('password');
        $password->setRequired(true)
            ->addValidator('NotEmpty', true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true)
            ->addErrorMessage('Password required')
            ->setAttrib('class', 'form-control')
            ->setAttrib('placeholder', 'Password')
            ->removeDecorator('HtmlTag')
            ->removeDecorator('Label');
        $this->addElement($password);
    }
}