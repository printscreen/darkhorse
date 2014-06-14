<?php

class Darkhorse_Endicia_Client
{
    const WSDL_URL = 'http://www.envmgr.com/LabelService/EwsLabelService.asmx?wsdl';


    const SERVICE_FIRST_CLASS = 'First';
    const SERVICE_PRIORITY = 'Priority';

    private $_client;
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

        $this->_client = new SoapClient(self::WSDL_URL, array('trace' => TRUE));
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

        $return = $this->_client->GetPostageLabel(array('LabelRequest' => array(
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
              , 'InsuredMail' => 'ENDICIA'
              , 'SignatureConfirmation' => 'OFF'
            )
          , 'Description' => 'Darkhorse Postage'
          , 'PartnerCustomerID' => '12345ABCD'
          , 'PartnerTransactionID' => '6789EFGH'
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

    public function cancelLabel(array $trackingNumberIds = array())
    {
        $return = $this->_client->GetRefund(array('RefundRequest' => array(
            'RequesterID' => $this->_requesterId
          , 'RequestID' => time()
          , 'AccountID' => $this->_accountId
          , 'PassPhrase' => $this->_passPhrase
          , 'Test' => 'Y'
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
}