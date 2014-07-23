<?php
class Model_Account extends Model_Base_Base
{
    private $_cache;
    protected $_accountId;
    protected $_serialNumber;
    protected $_postageBalance;
    protected $_ascendingBalance;
    protected $_accountStatus;
    protected $_deviceId;
    protected $_referenceId;

    public function __construct(array $options = array())
    {
        $this->_cache = Zend_Registry::get(CACHE);
    }

    public function getAccountBalance()
    {
        $accountBalance = $this->_cache->load('account');
        if(is_null($accountBalance) || !is_numeric($accountBalance)) {
            $accountBalance = $this->_query();
        }
        return $accountBalance;
    }

    public function buyPostage()
    {
        $account = new Darkhorse_Endicia_Client(array(
            'requesterId' => Zend_Registry::get(ENDICIA_REQUESTER_ID)
          , 'accountId' => Zend_Registry::get(ENDICIA_ACCOUNT_ID)
          , 'passPhrase' => Zend_Registry::get(ENDICIA_PASSPHRASE)
          , 'isTestEnv' => 'production' != APPLICATION_ENV
        ));
        $result = $account->buyPostage();
    }

    private function _query()
    {
        $account = new Darkhorse_Endicia_Client(array(
            'requesterId' => Zend_Registry::get(ENDICIA_REQUESTER_ID)
          , 'accountId' => Zend_Registry::get(ENDICIA_ACCOUNT_ID)
          , 'passPhrase' => Zend_Registry::get(ENDICIA_PASSPHRASE)
          , 'production' != APPLICATION_ENV
        ));
        $result = $account->getAccountStatus();
        if(!isset($result->AccountStatusResponse->CertifiedIntermediary)) {
            throw new Zend_Exception('Unable to get account status');
        }
        $account = $result->AccountStatusResponse->CertifiedIntermediary;
        $this->_accountId = $account->AccountID;
        $this->_serialNumber = $account->SerialNumber;
        $this->_postageBalance = $account->PostageBalance;
        $this->_ascendingBalance = $account->AscendingBalance;
        $this->_accountStatus = $account->AccountStatus;
        $this->_deviceId = $account->DeviceID;
        $this->_referenceId = $account->ReferenceID;

        $status = $this->toArray();
        $this->_cache->save($status, 'account');
        return $status;
    }
}