<?php

class UsersController extends Darkhorse_Controller_Action
{
    public function indexAction()
    {
        $this->view->headScript()
        ->prependFile('/js/default/users.js', $type = 'text/javascript' );
    }

    public function viewAction()
    {
        $users = new Model_Users();
        $users->getUsers(
            $this->getRequest()->getParam('active')
          , $this->getRequest()->getParam('sort')
          , $this->getRequest()->getParam('offset')
          , $this->getRequest()->getParam('limit')
        );

        $this->_helper->json(array(
            'success' => true,
            'users' => $users->toArray()
        ));
    }

    public function getUserAction()
    {
        $user = new Model_User(array(
            'userId' => $this->getRequest()->getParam('userId')
        ));
        $user->load();
        $this->_helper->json(array(
            'user' => $user->toArray()
        ));
    }

    public function editAction()
    {
        $form = new Form_User();
        $success = false;
        if ($form->isValid($this->getRequest()->getParams())) {
            $user = new Model_User(array(
                'userId' => $form->getElement('userId')->getValue()
              , 'firstName' => $form->getElement('firstName')->getValue()
              , 'lastName' => $form->getElement('lastName')->getValue()
              , 'email' => $form->getElement('email')->getValue()
              , 'userTypeId' => $form->getElement('userTypeId')->getValue()
              , 'active' => $form->getElement('active')->getValue()
            ));
            if(is_numeric($form->getElement('userId')->getValue())) {
                $user->update();
            } else {
                $user->insert($form->getElement('password')->getValue());
            }
            $success = true;
            $userId = $user->getUserId();
        }
        $this->_helper->json(array(
            'success' => $success,
            'userId' => $userId,
            'errors' => $form->getFormErrors()
        ));
    }
}
