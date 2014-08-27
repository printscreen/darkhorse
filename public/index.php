<?php


$soapclient = new soapclient('https://elstestserver.endicia.com/LabelService/EwsLabelService.asmx?wsdl');
$soapclient->__setLocation('https://elstestserver.endicia.com/LabelService/EwsLabelService.asmx?wsdl');

$xml = "";
$xml = $xml."<SCANRequest>";
$xml = $xml."<RequesterID>2501528/RequesterID>";
$xml = $xml."<RequestID>".time()."</RequestID>";
$xml = $xml."<AccountID>2501528</AccountID>";
$xml = $xml."<PassPhrase>anothercoolpassword</PassPhrase>";
$xml = $xml."<SCANList>
                <PICNumber>0400110200882100870222</PICNumber>
            </SCANList>";
$xml = $xml."</SCANRequest>";

$params = array(
    'XMLInput' => $xml
        );

$moo = $soapclient->__call('GetSCAN',$params);

echo 'Response:<br /><pre>';
echo var_dump($moo);


$xml = "";
$xml = $xml."<SCANRequest>";
$xml = $xml."<RequesterID>2501528/RequesterID>";
$xml = $xml."<RequestID>".time()."</RequestID>";
$xml = $xml."<CertifiedIntermediary>
                <AccountID>2501528</AccountID>
                <PassPhrase>anothercoolpassword</PassPhrase>
            </CertifiedIntermediary>";
$xml = $xml."<SCANList>
                <PICNumber>0400110200882100870222</PICNumber>
            </SCANList>";
$xml = $xml."</SCANRequest>";

$params = array(
    'XMLInput' => $xml
        );

$moo = $soapclient->__call('GetSCAN',$params);

echo 'Response:<br /><pre>';
echo var_dump($moo);

die;




/**
            'RequesterID' => $this->_requesterId
          , 'RequestID' => time()
          , 'AccountID' => $this->_accountId
          , 'PassPhrase' => $this->_passPhrase
          , 'CertifiedIntermediary' => array(
              'AccountID' => $this->_accountId,
              'PassPhrase' => $this->_passPhrase
          )
          , 'ImageFormat' => 'GIF'
          , 'FormType' => 5630
          , 'DPI' => 96
          , 'SearchZip' => 98133
          , 'FromName' => $params['fromName']
          , 'FromAddress' => $params['fromAddress']
          , 'FromCompany' => 'Darkhorse'
          , 'FromCity' => $params['fromCity']
          , 'FromState' => $params['fromState']
          , 'FromZipCode' => $params['fromZip']
          , 'PICNumbers' => $params['trackingNumbers']
          , 'SCANList' => array(
                'PICNumbers' => $params['trackingNumbers']
          )
        )));
*/





// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();