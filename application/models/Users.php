<?php
class Model_Users extends Model_Base_Db
{
    protected $_users;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);

        $this->_users = array();
    }

    public function getUsers($active = true, $sort = null, $offset = null, $limit = null)
    {
        $where = '';
        if(is_bool($active)) {
            $where .= 'AND active = :active';
        }
        $sql = "
            SELECT
                u.user_id
              , u.first_name
              , u.last_name
              , u.email
              , u.user_type_id
              , ut.name AS user_type_name
              , u.active
              , ( SELECT
                    count(*)
                  FROM users
                  WHERE true
                  $where
                ) AS total
            FROM users u
            INNER JOIN user_type ut ON u.user_type_id = ut.user_type_id
            WHERE true
            $where
            ORDER BY :sort
            LIMIT :offset,:limit
        ";

        $query = $this->_db->prepare($sql);

        $sort = $this->getSort($sort);
        $offset = $this->getOffset($offset);
        $limit = $this->getLimit($limit);
        $active = $this->convertFromBoolean($active);
        $query->bindParam(':sort', $sort, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        if(is_bool($active)) {
            $query->bindParam(':active', $active, PDO::PARAM_BOOL);
        }
        $query->execute();

        $result = $query->fetchAll();

        $this->_users = array();
        if(!empty($result)) {
            foreach($result as $key => $value) {
                $user = new Model_User();
                $user->loadRecord($value);
                $this->_users[] = $user;
            }
        }
        return $this->_users;
    }

    public function toArray()
    {
        $users = array();
        if(is_array($this->_users) && count($this->_users) > 0) {
            foreach($this->_users as $user) {
                $users[] = $user->toArray();
            }
        }
        return $users;
    }
}