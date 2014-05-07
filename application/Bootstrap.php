<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initDefines()
    {
        defined('DARKHORSE_DB') || define('DARKHORSE_DB', 'Darkhorse_Database');
        defined('TOKEN') || define('TOKEN', 'User_Token');
        defined('SALT') || define('SALT', 'With_my_last_breath_i_curse_Zoidberg!');
        defined('SESSION') || define('SESSION', 'Darkhorse_Session');
        defined('CACHE') || define('CACHE', 'Darkhorse_Cache');
        defined('SYSTEM_NAME') || define('SYSTEM_NAME', 'System_Name');
        defined('SYSTEM_EMAIL_ADDRESS') || define('SYSTEM_EMAIL_ADDRESS', 'System_Email_Address');
        defined('SYSTEM_MAILER') || define('SYSTEM_MAILER', 'System_Emailer_Object');
                defined('APPLICATION_URL') || define('APPLICATION_URL', 'Application_Url');
        defined('USER_TYPE_ADMIN') || define('USER_TYPE_ADMIN', 1);
        defined('USER_TYPE_EMPLOYEE') || define('USER_TYPE_EMPLOYEE', 2);
        defined('USPS_API_USERNAME') || define('USPS_API_USERNAME', 'Usps_Api_Username');
        defined('USPS_API_PASSWORD') || define('USPS_API_PASSWORD', 'Usps_Api_Password');
    }

    protected function _initAutoload()
    {
        $autoLoader = Zend_Loader_Autoloader::getInstance();
        $autoLoader->registerNamespace('Darkhorse_');
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath' => APPLICATION_PATH,
            'namespace' => '',
            'resourceTypes' => array(
                'form' => array(
                    'path' => '/modules/default/views/forms/',
                    'namespace' => 'Form_'
                ),
                'model' => array(
                    'path' => '/models/',
                    'namespace' => 'Model_'
                )
            )
        ));
    }

    protected function _initApplication()
    {
        date_default_timezone_set($this->getOption('default_time_zone'));
        Zend_Registry::set(SYSTEM_NAME, $this->getOption('application_name'));
        Zend_Registry::set(APPLICATION_URL, $this->getOption('application_url'));
    }

    protected function _initUSPS()
    {
        $usps = $this->getOption('usps');
        Zend_Registry::set(USPS_API_USERNAME, $usps['username']);
        Zend_Registry::set(USPS_API_PASSWORD, $usps['password']);
    }

    protected function _initDb()
    {
        $db = $this->getPluginResource('db')->getDbAdapter();
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        Zend_Registry::set(DARKHORSE_DB, $db);
    }

    protected function _initPlugins()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->registerPlugin(new Darkhorse_Controller_Plugin_Acl());
        $frontController->registerPlugin(new Darkhorse_Controller_Plugin_Init());
    }

    protected function _initSession()
    {
        $session = new Zend_Session_Namespace(SESSION);
        Zend_Registry::set(SESSION, $session);
    }

    protected function _initMailTransport()
    {
        $options = $this->getOption('mail');
        Zend_Registry::set(SYSTEM_EMAIL_ADDRESS, $options['system_address']);
        $mailer = new Zend_Mail_Transport_Smtp($options['server'], array(
                'ssl' => 'ssl',
                'port' => 465,
                'auth' => 'login',
                'username' => $options['user_name'],
                'password' => $options['password']
            )
        );
        Zend_Registry::set(SYSTEM_MAILER,$mailer);
    }

    protected function _initCcache()
    {
        $cache = Zend_Cache::factory(
                  'Core'
                , 'File'
                , array(
                      'caching' => true
                    , 'cache_id_prefix' => 'Darkhorse_'
                    , 'lifetime' => 7200
                    , 'logging' => true
                    , 'write_control' => true
                    , 'automatic_serialization' => true
                    , 'automatic_cleaning_factor' => 0
                    , 'ignore_user_abort' => false
                 )
                 , array(
                      'compression' => false
                    , 'compatibility' => false
                    , 'cacheDir' => '/tmp'
                )
             );
        Zend_Registry::set(CACHE,$cache);
    }
}