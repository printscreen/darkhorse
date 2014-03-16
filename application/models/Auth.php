<?php
class Model_Auth extends Model_Base_Db implements Zend_Auth_Adapter_Interface
{
    private $_email;
    private $_password;
    /**
     * Sets username and password for authentication
     *http://framework.zend.com/manual/en/zend.auth.html
     * @return void
     */
    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'email' => null,
            'password' => null,
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);
        $this->_email = $settings['email'];
        $this->_password = $settings['password'];
    }
    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot
     *                                     be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        if($this->isValidPassword()) {
            $atype = Zend_Auth_Result::SUCCESS;
            $identity = $this->_email;
        } else {
            $atype = Zend_Auth_Result::FAILURE;
            $identity = null;
        }

        $myresult = new Zend_Auth_Result($atype, $identity);
        return $myresult;
    }

    public function isValidPassword()
    {
        $query = $this->_db->prepare("SELECT EXISTS (SELECT true FROM users WHERE email = :email AND password = :password) AS auth ");
        $query->execute(array(
              ':email'=>mysql_real_escape_string($this->_email)
            , ':password'=> Model_User::hashPassword($this->_password)
        ));
        $result = $query->fetchAll();
        $success = $result[0]->auth;

        return (bool)$success;
    }
}