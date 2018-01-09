<?php
class mysqlpdo{
    protected $pdo;
    protected $res;
    protected $dsb;
    protected $name; 
    protected $pass; 
     
    /*构造函数*/
    function __construct($conn, $name, $pass){
        $this->dsb = $conn;
        $this->name = $name;  
        $this->pass = $pass;  
        $this->connect();
    }
     
    /*数据库连接*/
    public function connect(){
        try{
            $this->pdo = new PDO($this->dsb, $this->name, $this->pass);
            $this->pdo->query('set names utf8');     
        } catch(Exception $e) {
            echo $e->getMessage();
        } 
    }
     
    /*数据库关闭*/
    public function close(){
        $this->pdo = null;
    }
     
    public function query($sql){
        $res = $this->pdo->query($sql);
        if($res){
            $this->res = $res;
        }
    }

    public function exec($sql){
        $res = $this->pdo->exec($sql);
        if($res){
            $this->res = $res;
        } else {
            $this->res = 0;
        }
    }
    public function fetchAll($s = PDO::FETCH_ASSOC, $m = ''){
        return $this->res->fetchAll($s, $m);
    }
    public function fetch($s = ''){
        if($this->res){
            return $this->res->fetch($s);
        }else{
            return array();
        }
    }

    public function fetchObject (){
        if($this->res){
            return $this->res->fetchObject();
        }else{
            return false;
        }
    }
    

    public function fetchColumn(){
        return $this->res->fetchColumn();
    }
    public function getLastId(){
        return $this->pdo->lastInsertId();
    }
     
    /**
     * 参数说明
     * int              $debug      是否开启调试，开启则输出sql语句
     *                              0   不开启
     *                              1   开启
     *                              2   开启并终止程序
     * int              $mode       返回类型
     *                              0   返回多条记录
     *                              1   返回单条记录
     *                              2   返回行数
     *                              3   返回单条记录字段
     * string/array     $table      数据库表，两种传值模式
     *                              普通模式：
     *                              'tb_member, tb_money'
     *                              数组模式：
     *                              array('tb_member', 'tb_money')
     * string/array     $fields     需要查询的数据库字段，允许为空，默认为查找全部，两种传值模式
     *                              普通模式：
     *                              'username, password'
     *                              数组模式：
     *                              array('username', 'password')
     * string/array     $sqlwhere   查询条件，允许为空，两种传值模式
     *                              普通模式：
     *                              'and type = 1 and username like "%os%"'
     *                              数组模式：
     *                              array('type = 1', 'username like "%os%"')
     * string           $orderby    排序，默认为id倒序
     */
    public function getSelect($debug, $mode, $table, $fields="*", $sqlwhere="", $orderby="f_id desc", $limit = ''){
        //参数处理
        if(is_array($table)){
            $table = implode(', ', $table);
        }
        if(is_array($fields)){
            $fields = implode(', ', $fields);
        }
        if(is_array($sqlwhere)){
            $sqlwhere = ' and '.implode(' and ', $sqlwhere);
        }
        //数据库操作
        if($debug === 0){
            if($mode === 2){
                $this->query("select count(f_id) from $table where 1=1 $sqlwhere");
                $return = $this->fetchColumn();
            }else if($mode === 1){
                $this->query("select $fields from $table where 1=1 $sqlwhere order by $orderby");
                $return = $this->fetch();
            }else if($mode === 3) {
                $this->query("select $fields from $table where 1=1 $sqlwhere order by $orderby");
                $return_row = $this->fetch();
                $return = $return_row[$fields];
            }else{
                $this->query("select $fields from $table where 1=1 $sqlwhere order by $orderby $limit");
                $return = $this->fetchAll();
            }
            return $return;
        }else{
            if($mode === 2){
                echo "select count(f_id) from $table where 1=1 $sqlwhere";
            }else if($mode === 1){
                echo "select $fields from $table where 1=1 $sqlwhere order by $orderby";
            }
            else{
                echo "select $fields from $table where 1=1 $sqlwhere order by $orderby $limit";
            }
            if($debug === 2){
                exit;
            }
        }
    }
     
    /**
     * 参数说明
     * int              $debug      是否开启调试，开启则输出sql语句
     *                              0   不开启
     *                              1   开启
     *                              2   开启并终止程序
     * int              $mode       返回类型
     *                              0   无返回信息
     *                              1   返回执行条目数
     *                              2   返回最后一次插入记录的id
     * string/array     $table      数据库表，两种传值模式
     *                              普通模式：
     *                              'tb_member, tb_money'
     *                              数组模式：
     *                              array('tb_member', 'tb_money')
     * string/array     $set        需要插入的字段及内容，两种传值模式
     *                              普通模式：
     *                              'username = "test", type = 1, dt = now()'
     *                              数组模式：
     *                              array('username = "test"', 'type = 1', 'dt = now()')
     */
    public function setInsert($debug, $mode, $table, $set){
        //参数处理
        if(is_array($table)){
            $table = implode(', ', $table);
        }
        if(is_array($set)){
            $set = implode(', ', $set);
        }
        //数据库操作
        if($debug === 0){
            if($mode === 2){
                $this->query("insert into $table set $set");
                $return = $this->getLastId();
            }else if($mode === 1){
                $this->exec("insert into $table set $set");
                $return = $this->res;
            }else{
                $this->query("insert into $table set $set");
                $return = NULL;
            }
            return $return;
        }else{
            echo "insert into $table set $set";
            if($debug === 2){
                exit;
            }
        }
    }
     
    /**
     * 参数说明
     * int              $debug      是否开启调试，开启则输出sql语句
     *                              0   不开启
     *                              1   开启
     *                              2   开启并终止程序
     * int              $mode       返回类型
     *                              0   无返回信息
     *                              1   返回执行条目数
     * string           $table      数据库表，两种传值模式
     *                              普通模式：
     *                              'tb_member, tb_money'
     *                              数组模式：
     *                              array('tb_member', 'tb_money')
     * string/array     $set        需要更新的字段及内容，两种传值模式
     *                              普通模式：
     *                              'username = "test", type = 1, dt = now()'
     *                              数组模式：
     *                              array('username = "test"', 'type = 1', 'dt = now()')
     * string/array     $sqlwhere   修改条件，允许为空，两种传值模式
     *                              普通模式：
     *                              'and type = 1 and username like "%os%"'
     *                              数组模式：
     *                              array('type = 1', 'username like "%os%"')
     */
    public function setUpdate($debug, $mode, $table, $set, $sqlwhere=""){
        //参数处理
        if(is_array($table)){
            $table = implode(', ', $table);
        }
        if(is_array($set)){
            $set = implode(', ', $set);
        }
        if(is_array($sqlwhere)){
            $sqlwhere = ' and '.implode(' and ', $sqlwhere);
        }
        //数据库操作
        if($debug === 0){
            if($mode === 1){
                $this->exec("update $table set $set where 1=1 $sqlwhere");
                $return = $this->res;
            }else{
                $this->query("update $table set $set where 1=1 $sqlwhere");
                $return = NULL;
            }
            return $return;
        }else{
            echo "update $table set $set where 1=1 $sqlwhere";
            if($debug === 2){
                exit;
            }
        }
    }
     
    /**
     * 参数说明
     * int              $debug      是否开启调试，开启则输出sql语句
     *                              0   不开启
     *                              1   开启
     *                              2   开启并终止程序
     * int              $mode       返回类型
     *                              0   无返回信息
     *                              1   返回执行条目数
     * string           $table      数据库表
     * string/array     $sqlwhere   删除条件，允许为空，两种传值模式
     *                              普通模式：
     *                              'and type = 1 and username like "%os%"'
     *                              数组模式：
     *                              array('type = 1', 'username like "%os%"')
     */
    public function Set_Delete($debug, $mode, $table, $sqlwhere=""){
        //参数处理
        if(is_array($sqlwhere)){
            $sqlwhere = ' and '.implode(' and ', $sqlwhere);
        }
        //数据库操作
        if($debug === 0){
            if($mode === 1){
                $this->exec("delete from $table where 1=1 $sqlwhere");
                $return = $this->res;
            }else{
                $this->query("delete from $table where 1=1 $sqlwhere");
                $return = NULL;
            }
            return $return;
        }else{
            echo "delete from $table where 1=1 $sqlwhere";
            if($debug === 2){
                exit;
            }
        }
    }
}



