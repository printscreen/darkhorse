<?php
class Model_User extends Model_Base_Db
{
    const USER_TYPE_ADMIN = 1;
    const USER_TYPE_EMPLOYEE = 2;

    protected $_userId;
    protected $_firstName;
    protected $_lastName;
    protected $_email;
    protected $_userTypeId;
    protected $_active;
    protected $_total;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'userId' => null,
            'firstName' => null,
            'lastName' => null,
            'email' => null,
            'password' => null,
            'userTypeId' => null,
            'active' => null,
            'db' => null,
            ), $options);
        parent::__construct($settings['db']);
        $this->_userId = $settings['userId'];
        $this->_firstName = $settings['firstName'];
        $this->_lastName = $settings['lastName'];;
        $this->_email = $settings['email'];;
        $this->_userTypeId = $settings['userTypeId'];;
        $this->_active = $settings['active'];;
    }

    public function isActive()
    {
        return $this->_active;
    }

    public function loadRecord($record)
    {
        $this->_userId = $record->user_id;
        $this->_firstName = $record->first_name;
        $this->_lastName = $record->last_name;
        $this->_email = $record->email;
        $this->_userTypeId = $record->user_type_id;
        $this->_active = $record->active;
        $this->_total = $record->total;
    }

    public function loadUserIntoSession(&$session)
    {
        $session->userId = $this->_userId;
        $session->email = $this->_email;
        $session->firstName = $this->_firstName;
        $session->lastName = $this->_lastName;
        $session->userTypeId = $this->_userTypeId;
    }

    public function load()
    {
        $where = 'WHERE true';
        $binds = array();
        if(!empty($this->_userId) && is_numeric($this->_userId)) {
            $where .= ' AND user_id = :userId';
            $binds[':userId'] = $this->_userId;
        } else if(!empty($this->_email)) {
            $where .= ' AND email = :email';
            $binds[':email'] = $this->_email;
        } else {
            throw new Zend_Exception("No user id or email supplied");
        }

        $sql = "
            SELECT
                user_id
              , first_name
              , last_name
              , email
              , user_type_id
              , active
              , 1 AS total
            FROM users $where LIMIT 1
        ";
        $query = $this->_db->prepare($sql);
        $query->execute($binds);
        $result = $query->fetchAll();

        if(!$result || count($result) != 1) {
            return false;
        }

        $this->loadRecord($result[0]);
        return true;
    }

    public function insert($password = null)
    {
        $sql = "INSERT INTO users (
                    first_name
                  , last_name
                  , email
                  , password
                  , user_type_id
                  , active
                )
                VALUES (
                    :firstName
                  , :lastName
                  , :email
                  , :password
                  , :userTypeId
                  , :active
                )";
        $query = $this->_db->prepare($sql);

        $active = $this->convertFromBoolean($this->_active);
        $userTypeId = $this->convertToInt($this->_userTypeId);
        $password = self::hashPassword($password);

        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':firstName', $this->_firstName , PDO::PARAM_STR);
        $query->bindParam(':lastName', $this->_lastName , PDO::PARAM_STR);
        $query->bindParam(':email', $this->_email , PDO::PARAM_STR);
        $query->bindParam(':userTypeId', $userTypeId, PDO::PARAM_INT);
        $query->bindParam(':active', $active, PDO::PARAM_BOOL);

        $result = $query->execute();

        if(!$result) {
            return false;
        }
        $this->_userId = $this->_db->lastInsertId('users','user_id');

        return true;
    }

    public function update()
    {
        if(empty($this->_userId) || !is_numeric($this->_userId)) {
            throw new Zend_Exception('No user id supplied');
        }
        $sql = "UPDATE users SET
                    first_name = COALESCE(:firstName, first_name)
                  , last_name = COALESCE(:lastName, last_name)
                  , email = COALESCE(:email, email)
                  , user_type_id = COALESCE(:userTypeId, user_type_id)
                  , active = COALESCE(:active, active)
                  WHERE user_id = :userId;
                ";
        $query = $this->_db->prepare($sql);

        $userId = $this->convertToInt($this->_userId);
        $active = $this->convertFromBoolean($this->_active);
        $userTypeId = $this->convertToInt($this->_userTypeId);

        $query->bindParam(':userId', $userId, PDO::PARAM_INT);
        $query->bindParam(':firstName', $this->_firstName , PDO::PARAM_STR);
        $query->bindParam(':lastName', $this->_lastName , PDO::PARAM_STR);
        $query->bindParam(':email', $this->_email , PDO::PARAM_STR);
        $query->bindParam(':userTypeId', $userTypeId, PDO::PARAM_INT);
        $query->bindParam(':active', $active, PDO::PARAM_BOOL);
        $result = $query->execute();

        if(!$result) {
            return false;
        }
        return true;
    }

    public function updatePassword($password)
    {
        if(trim($password) == '' || !is_numeric($this->_userId)) {
            throw new Zend_Exception('No user id or password supplied');
        }
        $sql = "UPDATE users SET
                    password = :password
                  WHERE user_id = :userId;
                ";
        $query = $this->_db->prepare($sql);

        $password = $this->hashPassword($password);
        $userId = $this->convertToInt($this->_userId);

        $query->bindParam(':userId', $userId, PDO::PARAM_INT);
        $query->bindParam(':password', $password, PDO::PARAM_STR);

        $result = $query->execute();

        if(!$result) {
            return false;
        }
        return true;
    }

    public static function hashPassword($password)
    {
        if(empty($password)) {
            throw new Zend_Exception('Password can not be empty');
        }
        return md5(
            SALT.
            md5(
                SALT.
                trim($password).
                SALT
            ).
            SALT
        );
    }

    public function getResetPasswordToken()
    {
        if(!$this->_userId && !$this->load()) {
            throw new Zend_Exception('Unable to find user');
        }

        $sql = "SELECT md5(CONCAT(user_id,password,email)) AS token
                FROM users
                WHERE user_id = :userId";
        $query = $this->_db->prepare($sql);

        $userId = $this->convertToInt($this->_userId);
        $query->bindParam(':userId', $userId, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll();

        return current($result)->token;
    }

    //Setters
    public function setUserId($userId){$this->_userId = $userId; return $this;}
    public function setFirstName($firstName){$this->_firstName = $firstName; return $this;}
    public function setLastName($lastName){$this->_lastName = $lastName; return $this;}
    public function setEmail($email){$this->_email = $email; return $this;}
    public function setUserTypeId($userTypeId){$this->_userTypeId = $userTypeId; return $this;}
    public function setActive($active){$this->_active = $active; return $this;}

    //Getters
    public function getUserId(){return $this->_userId;}
    public function getFirstName(){return $this->_firstName;}
    public function getLastName(){return $this->_lastName;}
    public function getEmail(){return $this->_email;}
    public function getUserTypeId(){return $this->_userTypeId;}
    public function getActive(){return $this->_active;}
    public function getTotal(){return $this->_total;}
}