<?php

class DB{
    public $db;
    public $fields = '*';
    public $tablename = '';
    public $wheresql = '1=1';
    public $orderby = 'f_id desc';
    public $ofst = 0;
    public $limit = 1;
    public $setLimit = false;

    public $isFind = 0;

    public function __construct()
    {
        try {
            $this->db = new PDO(DB_DSN, DB_USER, DB_PASSWD);
            $this->db->query('set names utf8');     
        } catch(PDOException $e) {
            echo $e->getMessage();
            exit;
        } 
    }

    public function table($name)
    {
        $this->tablename = TBL_PREFIX . $name;
        return $this;
    }


    public function where()
    {
        $args = func_get_args();

        $m = $args[0];
        $n = isset($args[1]) ? $args[1] : null;
        $q = isset($args[2]) ? $args[2] : null;
        $where = [];
        $argsNum = func_num_args();
        if ($argsNum == 3) {
            $where[] = $m . ' ' . $n . " " . "'" . $q . "'";
        }
        if ($argsNum == 2) {
            $where[] = $m . '=' . "'" . addslashes($n) . "'";
        }
        if ($argsNum == 1) {
            if (is_array($m)) {
                foreach($m as $k => $v) {
                    $where[] = $k . '=' . "'" . addslashes($v) . "'";
                }
            }
        }
        if (count($where)) {
            if ($this->wheresql) {
                $this->wheresql .= ' AND ' . implode(' AND ', $where);
            } else {
                $this->wheresql .= implode(' AND ', $where);
            }
            $this->isFind = 1;
        } else {
            $this->wheresql .= '';
            $this->isFind = 0;
        }
        return $this;
    }

    public function orderBy($m = '', $n = '')
    {
        $argsNum = func_num_args();
        $orders = [];
        if ($argsNum == 2) {
            $this->orderby = $m . ' ' . $n;
        }
        if ($argsNum == 1) {
            if (is_array($m)) {
                foreach($m as $k => $v) {
                    $orders[] = $k . ' ' . $v;
                }
                $this->orderby = implode(',', $orders);
            } else {
                $this->orderby = $m;
            }

        }
        return $this;
    }


    public function skip($m = 0)
    {
        $this->ofst = $m;
        return $this;
    }

    public function take($m = 1)
    {
        $this->limit = $m;
        $this->setLimit = true;
        return $this;
    }

    public function first($fields = '')
    {
        $fields = is_array($fields) ? implode(',', $fields) : $fields;
        if ($fields) {
            $this->fields = $fields;
        }
        $sql = "SELECT " . $this->fields . " FROM " . $this->tablename . " WHERE " . $this->wheresql
                . " ORDER BY " . $this->orderby . " LIMIT " . $this->ofst . "," . $this->limit;
        $stmt = $this->db->query($sql);
        $this->checkError();
        if ($stmt) {
            $ret = $stmt->fetchObject();
        } else {
            $ret = [];
        }
        $this->db = null;
        return $ret;
    }


    public function get($fields = '')
    {

        $fields = is_array($fields) ? implode(',', $fields) : $fields;

        if ($fields) {
            $this->fields = $fields;
        }
        $limitsql = '';
        if ($this->setLimit) {
            $limitsql = " LIMIT " . $this->ofst . "," . $this->limit;
        }
        $sql = "SELECT " . $this->fields . " FROM " . $this->tablename . " WHERE " . $this->wheresql
                . " ORDER BY " . $this->orderby . $limitsql;
        $stmt = $this->db->query($sql);
        $this->checkError();
        if ($stmt) {
            $ret = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        } else {
            $ret = [];
        }
        $this->db = null;
        return $ret;
    }

    protected function checkError()
    {
        if ($this->db->errorCode() != '00000'){ 
            echo $this->tablename . ' '. $this->db->errorInfo()[2];exit; 
        }
    }


    public function save($data)
    {
        $set = [];
        $fields = [];
        $values = [];
        foreach($data as $k => $v) {
            $set[] = $k . '=' . "'" . addslashes($v) . "'";
            $fields[] = $k;
            $values[] = "'" . addslashes($v) . "'";
        }
        $_setsql = implode(', ', $set);
        $_fields = implode(',', $fields);
        $_values = implode(',', $values);

        if ($this->isFind) {
            $sql = 'UPDATE ' . $this->tablename . ' SET ' . $_setsql . ' WHERE ' . $this->wheresql;
            $ret = $this->db->exec($sql);
            $this->checkError();
            if (false !== $ret) {
                $ret = true;
            }
        } else {
            $sql = 'INSERT INTO ' . $this->tablename . '(' . $_fields . ') VALUES(' . $_values . ')';
            $this->db->exec($sql);
            $this->checkError();
            $ret = $this->db->lastInsertId();
        }

        $this->db = null;
        return $ret;
    }

    public function usedb($database)
    {
        $this->db->query('use ' . $database);
        return $this;
    }

    public function update(Array $data)
    {
        return $this->save($data);
    }

}



