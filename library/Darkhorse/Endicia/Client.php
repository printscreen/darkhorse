<?php

class Darkhorse_Endicia_Client
{
    const PROD_CORE_WSDL_URL = 'https://labelserver.endicia.com/LabelService/EwsLabelService.asmx?wsdl';
    const PROD_SERVICES_WSDL_URL = 'https://www.endicia.com/ELS/ELSServices.cfc?wsdl';
    const DEV_CORE_WSDL_URL = 'https://elstestserver.endicia.com/LabelService/EwsLabelService.asmx?wsdl';
    const DEV_SERVICES_WSDL_URL = 'https://elstestserver.endicia.com/ELS/ELSServices.cfc?wsdl';

    const SERVICE_FIRST_CLASS = 'First';
    const SERVICE_PRIORITY = 'Priority';

    private $_coreClient;
    private $_serviceClient;
    private $_requesterId;
    private $_accountId;
    private $_passPhrase;
    private $_isTestEnv;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'requesterId' => null,
            'accountId' => null,
            'passPhrase' => null,
            'isTestEnv' => null,
            ), $options);
        $this->_requesterId = $settings['requesterId'];
        $this->_accountId = $settings['accountId'];
        $this->_passPhrase = $settings['passPhrase'];
        $this->_isTestEnv = $settings['isTestEnv'] === true ? 'YES' : 'NO';
        $this->_coreWsdl = $settings['isTestEnv'] ? self::DEV_CORE_WSDL_URL : self::PROD_CORE_WSDL_URL;
        $this->_serviceWsdl = $settings['isTestEnv'] ? self::DEV_SERVICES_WSDL_URL : self::PROD_SERVICES_WSDL_URL;
    }

    private function getCoreClient()
    {
        if(!$this->_coreClient) {
          $this->_coreClient = new SoapClient($this->_coreWsdl);
          $this->_coreClient->__setLocation('https://elstestserver.endicia.com/LabelService/EwsLabelService.asmx?wsdl');
        }
        return $this->_coreClient;
    }

    private function getServiceClient()
    {
        if(!$this->_serviceClient) {
            $this->_serviceClient = new SoapClient($this->_serviceWsdl);
            $this->_serviceClient->__setLocation($this->_serviceWsdl);
        }
        return $this->_serviceClient;
    }

    public function getLabel(array $options = array())
    {
        $params = array_merge(array(
            'mailClass' => null
          , 'weightOunces' => null
          , 'value' => null
          , 'toName' => null
          , 'toCompany' => null
          , 'toAddress' => null
          , 'toAddress2' => null
          , 'toCity' => null
          , 'toState' => null
          , 'toZip' => null
          , 'toCountry' => null
          , 'toPhone' => null
          , 'fromName' => null
          , 'fromAddress' => null
          , 'fromCity' => null
          , 'fromState' => null
          , 'fromZip' => null
          , 'fromPhone' => null
        ), $options);

        $return = self::getCoreClient()->GetPostageLabel(array('LabelRequest' => array(
            'RequesterID' => $this->_requesterId
          , 'AccountID' => $this->_accountId
          , 'PassPhrase' => $this->_passPhrase
          , 'MailClass' => $params['mailClass']
          , 'DateAdvance' => 0
          , 'WeightOz' => $params['weightOunces']
          , 'CostCenter' => 0
          , 'Value' => empty($params['value']) ? '1.00' : $params['value']
          , 'Services' => array(
                'CertifiedMail' => 'OFF'
              , 'DeliveryConfirmation' => 'OFF'
              , 'ElectronicReturnReceipt' => 'OFF'
              , 'InsuredMail' => 'OFF'
              , 'SignatureConfirmation' => 'OFF'
            )
          , 'Description' => 'Darkhorse Postage'
          , 'PartnerCustomerID' => $this->_accountId
          , 'PartnerTransactionID' => time()
          , 'OriginCountry' => 'United States'
          , 'ToName' => $params['toName']
          , 'ToCompany' => $params['toCompany']
          , 'ToAddress1' => $params['toAddress']
          , 'ToAddress2' => $params['toAddress2']
          , 'ToCity' => $params['toCity']
          , 'ToState' => $params['toState']
          , 'ToPostalCode' => $params['toZip']
          , 'ToCountry' => $params['toCountry']
          , 'ToPhone' => $params['toPhone']
          , 'FromName' => $params['fromName']
          , 'ReturnAddress1' => $params['fromAddress']
          , 'FromCity' => $params['fromCity']
          , 'FromState' => $params['fromState']
          , 'FromPostalCode' => $params['fromZip']
          , 'FromZIP4' => ''
          , 'FromPhone' => $params['fromPhone']
          , 'CustomsQuantity1' => 0
          , 'CustomsValue1' => 0
          , 'CustomsWeight1' => 0
          , 'CustomsQuantity2' => 0
          , 'CustomsValue2' => 0
          , 'CustomsWeight2' => 0
          , 'CustomsQuantity3' => 0
          , 'CustomsValue3' => 0
          , 'CustomsWeight3' => 0
          , 'CustomsQuantity4' => 0
          , 'CustomsValue4' => 0
          , 'CustomsWeight4' => 0
          , 'CustomsQuantity5' => 0
          , 'CustomsValue5' => 0
          , 'CustomsWeight5' => 0
          , 'Test' => $this->_isTestEnv
          , 'LabelSize' => '4x6'
          , 'LabelType' => 'Default'
          , 'ImageFormat' => 'GIF'
        )));

        if(intval($return->LabelRequestResponse->Status) != 0) {
            throw new Darkhorse_Endicia_Exception(
                "Error: ".$return->LabelRequestResponse->Status.
                " Message: ".$return->LabelRequestResponse->ErrorMessage
            );
        }

        return $return->LabelRequestResponse;
    }

    public function buyPostage($amount)
    {
        $return = self::getCoreClient()->BuyPostage(array('RecreditRequest' => array(
            'RequesterID' => $this->_requesterId
          , 'RequestID' => time()
          , 'CertifiedIntermediary' => array(
              'AccountID' => $this->_accountId,
              'PassPhrase' => $this->_passPhrase
          )
          , 'RecreditAmount' => $amount
        )));
        return $return;
    }


    public function cancelLabel(array $trackingNumberIds = array())
    {
        $return = self::getCoreClient()->GetRefund(array('RefundRequest' => array(
            'RequesterID' => $this->_requesterId
          , 'RequestID' => time()
          , 'AccountID' => $this->_accountId
          , 'PassPhrase' => $this->_passPhrase
          , 'Test' => $this->_isTestEnv
          , 'RefundList' => array(
                'PICNumber' => $trackingNumberIds
          )
        )));
        $list = array();
        $refunds = isset($result->RefundResponse->RefundList->PICNumber) ? $result->RefundResponse->RefundList->PICNumber : null;
        //If a single PIC Number is passed, it wont return an array,
        //To cut down on logic, just make all data an array
        if(!empty($refunds) && !is_array($refunds)) {
            $refunds = array($refunds);
        }
        if(is_array($refunds) && count($refunds)) {
            for($i = 0; $i < count($refunds); $i++) {
                if(strtoupper($refunds[$i]->IsApproved) == 'YES') {
                    $list['approved'][] = $trackingNumberIds[$i];
                } else {
                    $list['rejected'][] = $trackingNumberIds[$i];
                }
            }
        }
        return $list;
    }

    public function getScanForm(array $params = array())
    {
        $return = self::getCoreClient()->GetSCAN(array('SCANRequest' => array(
            'RequesterID' => $this->_requesterId
          , 'RequestID' => time()
          , 'CertifiedIntermediary' => array(
              'AccountID' => $this->_accountId,
              'PassPhrase' => $this->_passPhrase
          )
          , 'Test' => $this->_isTestEnv
          , 'FromName' => $params['fromName']
          , 'FromAddress' => $params['fromAddress']
          , 'FromCity' => $params['fromCity']
          , 'FromState' => $params['fromState']
          , 'FromZipCode' => $params['fromZip']
          , 'SCANList' => array(
                'PICNumber' => $params['trackingNumbers']
          )
        )));

        return $return;
    }

    public function getAccountStatus()
    {
        $return = self::getCoreClient()->GetAccountStatus(array('AccountStatusRequest' => array(
            'RequesterID' => $this->_requesterId
          , 'RequestID' => time()
          , 'CertifiedIntermediary' => array(
              'AccountID' => $this->_accountId,
              'PassPhrase' => $this->_passPhrase
          )
        )));

        return $return;
    }
}

