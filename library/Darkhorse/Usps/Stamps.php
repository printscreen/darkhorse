<?php

require_once ('library/USPSOpenDistributeLabel.php');
require_once ('library/USPSAddressVerify.php');

class Darkhorse_Usps_Stamps
{
    protected $_apiUsername;
    protected $_apiPassword;
    protected $_testMode;

    public function __construct($options)
    {
        $settings = array_merge(array(
            'apiUsername' => null,
            'apiPassword' => null,
            'testMode' => true
            ), $options);
        $this->_apiUsername = $settings['apiUsername'];
        $this->_apiPassword = $settings['apiPassword'];
        $this->_testMode = $settings['testMode'];
    }

    public function printLabel($fromAddress, $toAddress, $weight)
    {
        $label = new USPSOpenDistributeLabel($this->_apiUsername);

        $label->setFromAddress(
            $fromAddress['firstName']
          , $fromAddress['lastName']
          , $fromAddress['company']
          , $fromAddress['address']
          , $fromAddress['city']
          , $fromAddress['state']
          , $fromAddress['postalCode']
          , isset($fromAddress['address_line_two']) ? $fromAddress['address_line_two'] : null
        );

        $label->setToAddress(
            $toAddress['name']
          , $toAddress['address']
          , $toAddress['city']
          , $toAddress['state']
          , $toAddress['postalCode']
          , null
          , $toAddress['addressLineTwo']
        );

        $label->setWeightOunces($weight);

        $label->createLabel();

        if(!$label->isSuccess()) {
            throw new Zend_Exception($label->getErrorMessage());
        }

        return $label->getArrayResponse();
    }

    public function verifyAddress($toAddress)
    {
        $address = new USPSAddress;
        $address->setFirmName($toAddress['firstName'] . ' ' . $toAddress['lastName']);
        $address->setApt($toAddress['addressLineTwo']);
        $address->setAddress($toAddress['address']);
        $address->setCity($toAddress['city']);
        $address->setState($toAddress['state']);
        $address->setZip5($toAddress['postalCode']);
        $address->setZip4('');

        $verify = new USPSAddressVerify($this->_apiUsername);
        $verify->setTestMode($this->_testMode);
        $verify->addAddress($address);
        $verify->verify();

        if(!$verify->isSuccess()) {
            throw new Zend_Exception('Unable to verify address');
        }

        return $verify->getArrayResponse();
    }
}