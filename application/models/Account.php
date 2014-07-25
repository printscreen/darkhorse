<?php
class Model_Account extends Model_Base_Base
{
    private $_cache;

    public function __construct(array $options = array())
    {
        $this->_cache = Zend_Registry::get(CACHE);
    }

    public function getAccountBalance()
    {
        $accountBalance = $this->_cache->load('account_balance');
        if(!is_null($accountBalance) && is_numeric($accountBalance)) {
            return $accountBalance;
        }
        $account = new Darkhorse_Endicia_Client(array(
            'requesterId' => Zend_Registry::get(ENDICIA_REQUESTER_ID)
          , 'accountId' => Zend_Registry::get(ENDICIA_ACCOUNT_ID)
          , 'passPhrase' => Zend_Registry::get(ENDICIA_PASSPHRASE)
          , 'production' != APPLICATION_ENV
        ));
        $result = $account->getAccountStatus();
        if(isset($result->AccountStatusResponse->ErrorMessage)) {
            throw new Zend_Exception($result->AccountStatusResponse->ErrorMessage);
        }

        $this->_cache->save(
            $result->AccountStatusResponse->CertifiedIntermediary->PostageBalance
          , 'account_balance'
        );

        return $result->AccountStatusResponse->CertifiedIntermediary->PostageBalance;
    }

    public function buyPostage($amount)
    {
        $account = new Darkhorse_Endicia_Client(array(
            'requesterId' => Zend_Registry::get(ENDICIA_REQUESTER_ID)
          , 'accountId' => Zend_Registry::get(ENDICIA_ACCOUNT_ID)
          , 'passPhrase' => Zend_Registry::get(ENDICIA_PASSPHRASE)
          , 'isTestEnv' => 'production' != APPLICATION_ENV
        ));
        $result = $account->buyPostage($amount);

        if(isset($result->RecreditRequestResponse->ErrorMessage)) {
            throw new Zend_Exception($result->RecreditRequestResponse->ErrorMessage);
        }

        $this->_cache->save(
            $result->RecreditRequestResponse->CertifiedIntermediary->PostageBalance
          , 'account_balance'
        );

        return $result->RecreditRequestResponse->CertifiedIntermediary->PostageBalance;
    }
}