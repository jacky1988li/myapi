<?php

class MyRedis {
    public static $db = null;

    //connect to the database
    public static function connect() {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        if(ONLINE) {
            $redis->auth(REDIS_PASS);
        }
        self::$db = $redis;
    }

    //close the connection
    public static function close() {
        if (self::$db) {
            self::$db->close();
        }
    }

    // 批量向redis hash表插入数据
    function setHashArrayValueToRedis($hashTableName, $array){
        if (!self::$db) self::connect();
        return self::$db->hMset($hashTableName, $array);
    }

    /**
     * 将哈希表 hashTableName 中的域 field 的值设为 value
     * 如果 hashTableName 不存在，一个新的哈希表被创建并进行 HSET 操作
     * 如果域 field 已经存在于哈希表中，旧值将被覆盖
     */
    function setValueToHashTable($hashTableName, $field, $value){
        if (!self::$db) self::connect();
        return self::$db->hSet($hashTableName, $field, $value);
    }

    // 获取redis hash表所有数据
    function getAllValueFromHashTable($hashTableName){
        if (!self::$db) self::connect();
        return self::$db->hGetAll($hashTableName);
    }

    // 获取redis所有key
    function getKeysFromRedis($hashTableName){
        if (!self::$db) self::connect();
        return self::$db->keys($hashTableName);
    }

    // 获取redis hash表所有key
    function getKeysFromHashTable($hashTableName){
        if (!self::$db) self::connect();
        return self::$db->hKeys($hashTableName);
    }

    // 获取redis hash表单个数据
    function getValueFromHashTable($hashTableName, $field){
        if (!self::$db) self::connect();
        return self::$db->hGet($hashTableName, $field);
    }

    // 递增redis hash表单个数据
    function incrByHashTable($hashTableName, $field, $num){
        if (!self::$db) self::connect();
        /*
        $arr = $this->getAllValueFromHashTable($hashTableName);
        if (!empty($arr)) {
            self::$db->hIncrBy($hashTableName, $field, $num);
        }*/
        if ($this->isKeyExist($hashTableName, $field)) {
            return self::$db->hIncrBy($hashTableName, $field, $num);
        }
    }

    // 查看redis表 key 是否存在
    function isKeyExist($hash, $key){
        if (!self::$db) self::connect();
        return self::$db->hExists($hash,$key);
    }

    // redis watch
    function watch($key){
        if (!self::$db) self::connect();
        return self::$db->watch($key);
    }

    // redis multi
    function multi(){
        if (!self::$db) self::connect();
        return self::$db->multi();
    }

    function exec(){
        if (!self::$db) self::connect();
        return self::$db->exec();
    }

    // 获取redis hashtable 所有value
    function getAllValuesFromHashTable($hashTableName){
        if (!self::$db) self::connect();
        return self::$db->hVals($hashTableName);
    }

    // 删除hashtable中的某个值
    function removeHashTableField($hashTableName, $field){
        if (!self::$db) self::connect();
        return self::$db->hDel($hashTableName, $field);
    }

    // 删除整个hashtable
    function deleteHashTable($hashTableName){
        if (!self::$db) self::connect();
        return self::$db->delete($hashTableName);
    }

    //判断指定的键是否存在
    function isKeyExists($keyname)
    {
        if (!self::$db) self::connect();
        return self::$db->exists($keyname);
    }

    //插入排序信息
    function setZaddValueTable($keyname, $score, $member)
    {
        if (!self::$db) self::connect();
        return self::$db->zAdd($keyname, $score, $member);
    }

    //删除排序信息
    function  deleteZremTable($keyname,$member)
    {
        if (!self::$db) self::connect();
        return self::$db->zRem($keyname, $member);
    }

    //获取排序信息列表
    function  getZrevrangeTable($keyname, $start=0, $stop=-1)
    {
        if (!self::$db) self::connect();
        return self::$db->zRangeByScore($keyname,$start,$stop);
    }

    //添加用户
    function  setUidSetTable($keyname, $value)
    {
        if (!self::$db) self::connect();
        return self::$db->SET($keyname, $value);
    }

    //获取用户
    function  getUidSetTable($keyname)
    {
        if (!self::$db) self::connect();
        return self::$db->GET($keyname);
    }


    //用户访问次数加1
    function  setUpdateUidTable($keyname)
    {
        if (!self::$db) self::connect();
        return self::$db->INCRBY($keyname, 1);
    }

    //添加用户访问次数有效时间
    function  setSetexTable($keyname, $time , $val=1)
    {
        if (!self::$db) self::connect();
        self::$db->INCRBY($keyname, $val);
        return self::$db->expire($keyname,$time);

    }

    //lpush
    function  setlPush($keyname,  $val)
    {
        if (!self::$db) self::connect();
        return self::$db->lPush($keyname,$val);
    }

    //lRange
    function getLrange($keyname)
    {
        if (!self::$db) self::connect();
        return self::$db->lRange($keyname, 0, -1);
    }

    //sort
    function lSort($keyname, $arr = array())
    {
    	if (!self::$db) self::connect();
    	return self::$db->sort($keyname, $arr);
    }

    //添加请求的广告信息
    function  setRequestid($keyname, $time, $value)
    {
    	if (!self::$db) self::connect();
    	self::$db->SET($keyname, $value);
    	return self::$db->expire($keyname,$time);
    
    }

    //添加返回成功广告信息 hash
    function setAdvHash($hashTableName, $field, $value, $time){
        if (!self::$db) self::connect();
        self::$db->hSet($hashTableName, $field, $value);
        return self::$db->expire($hashTableName,$time);
    }

    //删除redis 所有key
    function  flushDb()
    {
    	if (!self::$db) self::connect();
    	return self::$db->flushdb();
    
    }

}

