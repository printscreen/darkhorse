<?php
class Model_Base_Db extends Model_Base_Base
{
    protected $_db;

    public function __construct(Zend_Db_Adapter_Abstract $db = null)
    {
        if(!empty($db)) {
            $this->_db = $db;
        } else {
            $this->_db = Zend_Registry::get(DARKHORSE_DB);
        }
    }

    protected function getDirection($sort = null)
    {
        if(empty($sort) || !is_numeric($sort)) {
            return 'ASC';
        } else {
            return intval($sort) < 0 ? 'DESC' : 'ASC';
        }
    }

    protected function getSort($sort = null) {
        if(empty($sort) || !is_numeric($sort)) {
            return 1;
        } else {
            return abs($sort);
        }
    }

    protected function getOffset($offset = null) {
        if(is_null($offset) || !is_numeric($offset)) {
            return 0;
        } else {
            return intval($offset);
        }
    }

    protected function getLimit($limit = null) {
        if(empty($limit) || !is_numeric($limit)) {
            return PHP_INT_MAX;
        } else {
            return intval($limit);
        }
    }

    protected function convertFromBoolean($value)
    {
        if(is_bool($value) && $value) {
            return 1;
        } elseif(is_null($value)) {
            return null;
        } else {
            return strtolower(trim($value)) == 'true' ? 1:0;
        }
    }

    protected function convertToInt($value)
    {
        if(is_numeric($value)) {
            return intval($value);
        } else {
            return null;
        }
    }

    protected function arrayToSqlArray($array)
    {
        if(empty($array) && !is_array($array)) {
            return null;
        }
        $result = '{' . implode(',',$array) . '}';
        return $result;
    }

    protected function arrayToIn($array)
    {
        if(empty($array) && !is_array($array)) {
            return null;
        }
        $newVals = array();
        foreach($array as $val) {
            $newVals[] = ':'.$val;
        }
        return implode(',',$newVals);
    }

    public static function bind(&$query, array $binds)
    {
        foreach($binds as $key => $val) {
            $query->bindParam($key, $val['value'], $val['type']);
        }
    }
}