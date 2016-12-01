<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CustomerDatabase {

    private $pdo;

    public function __construct() {
        try {
            $config = StaticConf::$DBCONF;
            $this->pdo = new PDO('mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['dbname'], $config['user'], $config['pass']);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function getCustomerList($filters = array()) {

        $custSql = 'SELECT c.* FROM customer AS c ';
        $filter = $this->processFilters($filters);
        $custSql .= $filter['sql'];
        $statement = $this->pdo->prepare($custSql);
        $statement->execute($filter['params']);
        $resultArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        $custArray = $this->getCustListFromResult($resultArray);
        $custArray = $this->getCustomerData($custArray);
        $custArray = $this->getCustomerNumber($custArray);
        return array_values($custArray);
    }

    private function processFilters($filters) {
        $return = array(
          "params" => array(),
          "sql" => ""
        );
        $joinSql = "";
        $termSql = " WHERE ";
        $sortSql = "";
        foreach ($filters as $filterKey => $filterValue) {
            if (!in_array($filterKey, array("sort", "limit", "offset", "direction"))) {
                if (in_array($filterKey, array("id", "email"))) {
                    if (preg_match("/^[*]{1}[a-z A-z0-9]+[*]{1}$/", $filterValue) > 0) {
                        $termSql .= $filterKey . " LIKE :" . $filterKey . ' AND';
                        $return['params'][":" . $filterKey] = str_replace("*", "%", $filterValue);
                    } else {
                        $termSql .= $filterKey . " = :" . $filterKey . ' AND';
                        $return['params'][":" . $filterKey] = $filterValue;
                    }
                } else {
                    if ($this->isValidQueryValue($filterKey) && $this->isValidQueryValue($filterValue)) {
                        $joinSql .= " JOIN customerdetail AS c$filterKey ON c.id = c$filterKey.customer_id ";
                        $termSql .= " c$filterKey.identifiername = :c$filterKey AND";
                        if (preg_match("/^[*]{1}[a-z A-z0-9]+[*]{1}$/", $filterValue) > 0) {
                            $termSql .= " c$filterKey.identifiervalue LIKE :c" . $filterKey . "val AND";
                            $return['params'][":c" . $filterKey . 'val'] = str_replace("*", "%", $filterValue);

                        } else {
                            $termSql .= " c$filterKey.identifiervalue = :c".$filterKey."val AND";
                            $return['params'][":c" . $filterKey . 'val'] = $filterValue;
                        }
                        $return['params'][":c" . $filterKey] = $filterKey;
                    }
                }
            }
        }
        if (isset($filters['sort']) && in_array($filters['sort'], array("id", "email"))) {
            $sortSql .= " ORDER BY c." . $filters['sort'];
            if (isset($filters['direction']) && in_array(strtoupper($filters['direction']), array("ASC", "DESC"))) {
                $sortSql .= " " . strtoupper($filters['direction']);
            }
        }
        if (isset($filters['limit']) && is_numeric($filters['limit'])) {
            $sortSql .= " LIMIT " . $filters['limit'];
        }
        if (isset($filters['offset']) && is_numeric($filters['offset'])) {
            $sortSql .= " OFFSET " . $filters['offset'];
        }

        if (sizeof($return['params']) > 0) {
            $termSql = substr($termSql, 0, strlen($termSql) - 3);
        } else {
            $termSql = "";
        }
        $custSql = $joinSql . $termSql . $sortSql;
        $return['sql'] = $custSql;
        return $return;
    }

    public function isValidQueryValue($identifier) {
        if (ctype_alnum($identifier) || (preg_match("/^[*]{1}[a-z A-z0-9]+[*]{1}$/", $identifier) > 0)) {
            return true;
        } else {
            return false;
        }
    }

    private function getCustomerData($custArray) {
        $dataSql = 'SELECT * FROM customerdetail WHERE customer_id IN (' . implode(array_keys($custArray), ',') . ')';
        $statement = $this->pdo->prepare($dataSql);
        $statement->execute();
        $sqlRes = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($sqlRes as $rows) {
            if (isset($custArray[$rows['customer_id']])) {
                $custArray[$rows['customer_id']]->addDataAttribute($rows['identifiername'], $rows['identifiervalue']);
            }
        }
        return $custArray;
    }

    private function getCustomerNumber($custArray) {
        $dataSql = 'SELECT * FROM phonenumber WHERE customer_id IN (' . implode(array_keys($custArray), ',') . ')';
        $statement = $this->pdo->prepare($dataSql);
        $statement->execute();
        $sqlRes = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($sqlRes as $rows) {
            if (isset($custArray[$rows['customer_id']])) {
                $custArray[$rows['customer_id']]->setHashedNumber($rows['phonenumber']);
            }
        }
        return $custArray;
    }

    public function getSingleCustomer($id) {
        $custSql = 'select * from customer where id=:id';
        $statement = $this->pdo->prepare($custSql);
        $statement->execute(array("id" => $id));
        $resultArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        $custArray = $this->getCustListFromResult($resultArray);
        $custArray = $this->getCustomerData($custArray);
        $custArray = $this->getCustomerNumber($custArray);
        return $custArray;
    }

    public function getCustListFromResult($sqlRes) {
        $custArray = array();
        foreach ($sqlRes as $row) {
            $custArray[$row['id']] = new Customer($row['id'], $row['email']);
        }
        return $custArray;
    }

    public function addCustomer($postData) {
        $createNewCust = 'INSERT INTO customer (email) VALUES (:email)';
        $statement = $this->pdo->prepare($createNewCust);
        $statement->execute(array(":email" => $postData['email']));

        if ($statement->errorCode() != "00000") {
            $errifo = $statement->errorInfo();
            throw new Exception($errifo[2], 400);
        }
        $customer = new Customer($this->pdo->lastInsertId(), $postData['email']);
        unset($postData['email']);

        $password = substr(md5($customer->getId()), 0, 10) . substr(sha1($customer->getId()), 0, 10) . StaticConf::$ENCRYPTION_KEY;
        $phone = $customer->encrypt($password, $postData['phone']);
        $customer->setHashedNumber($phone);
        $this->insertPhoneNumber($customer->getId(), $phone);
        unset($postData['phone']);

        foreach ($postData as $k => $v) {
            if (ctype_alnum($k)) {
                $this->insertDataAttribute($customer->getId(), $k, $v);
                $customer->addDataAttribute($k, $v);
            } else {
                throw new InvalidDataException("Only alphanumeric data properties allowed! ");
            }
        }

        return $customer;
    }

    public function insertPhoneNumber($customerId, $phoneNumber) {
        $sql = 'INSERT INTO phonenumber (customer_id, phonenumber ) VALUES (:custid,:phone)';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(":custid" => $customerId,
          ":phone" => $phoneNumber));
        if ($statement->errorCode() != "00000") {
            $errifo = $statement->errorInfo();
            throw new Exception($errifo[2], 400);
        }
    }

    public function insertDataAttribute($customerId, $key, $val) {
        $sql = 'INSERT INTO customerdetail (customer_id, identifiername, identifiervalue) VALUES (:custid,:key,:val)';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(":custid" => $customerId,
          ":key" => $key,
          ":val" => $val));
        if ($statement->errorCode() != "00000") {
            $errifo = $statement->errorInfo();
            throw new Exception($errifo[2], 400);
        }
    }

}
