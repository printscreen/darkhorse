<?php

class AccountController extends Darkhorse_Controller_Action
{
    public function getStatusAction()
    {
        $account = new Model_Account();
        $this->_helper->json(array(
            'success' => true
          , 'balance' => $account->getAccountBalance()
        ));
    }

    public function buyPostageAction()
    {
        $account = new Model_Account();
        $balance = $account->buyPostage(
            $this->getRequest()->getParam('amount')
        );
        $this->_helper->json(array(
            'success' => true
          , 'balance' => $balance
        ));
    }
}