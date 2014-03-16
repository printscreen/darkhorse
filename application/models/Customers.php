<?php
class Model_Customers extends Model_Base_Db
{
    protected $_customers;

    public function __construct(array $options = array())
    {
        $settings = array_merge(array(
            'db' => null,
            ), $options);

        parent::__construct($settings['db']);

        $this->_customers = array();
    }

    public function getCustomers($active = true, $sort = null, $offset = null, $limit = null)
    {
        $where = '';
        if(is_bool($active)) {
            $where .= 'AND active = :active';
        }
        $sql = "
            SELECT
                c.customer_id
              , c.name
              , c.active
              , ( SELECT
                    count(*)
                  FROM customer
                  WHERE true
                  $where
                ) AS total
            FROM customer c
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

        $this->_customers = array();
        if(!empty($result)) {
            foreach($result as $key => $value) {
                $customer = new Model_Customer();
                $customer->loadRecord($value);
                $this->_customers[] = $customer;
            }
        }
        return $this->_customers;
    }

    public function toArray()
    {
        $customers = array();
        if(is_array($this->_customers) && count($this->_customers) > 0) {
            foreach($this->_customers as $customer) {
                $customers[] = $customer->toArray();
            }
        }
        return $customers;
    }
}