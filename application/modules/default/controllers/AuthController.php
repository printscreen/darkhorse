<?php

class AuthController extends Darkhorse_Controller_Action
{
    public function loginAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('auth');
        $form = new Form_Login();
        $success = false;
        $message = '';
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getParams())) {
            $auth = Zend_Auth::getInstance();
            // Set up the authentication adapter
            $authAdapter = new Model_Auth(array(
                  'email' => $form->getElement('email')->getValue()
                , 'password' => $form->getElement('password')->getValue()
            ));
            $result = $auth->authenticate($authAdapter);
            if($result->isValid()) {
                $session = Zend_Registry::get(SESSION);
                $user = new Model_User(array(
                    'email' => $form->getElement('email')->getValue()
                ));
                $user->load();
                $user->loadUserIntoSession($session);
                $this->_redirect('/');
            } else {
                $message = 'Wrong Email/Password';
            }
        } else {
            $errors = $form->getMessages();
            if(is_array($errors) && count($errors) > 0) {
                foreach($errors as $error) {
                    $reason = $error[0];
                    break;
                }
            }
        }
        $this->view->form = $form;
        $this->view->message = $message;
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
        $this->_redirect('/');
    }
}
