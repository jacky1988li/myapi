<?php

class OpRedis extends MyRedis {

    public function __construct()
    {
        $this->connect();
    }

    public function __destruct()
    {
        $this->close();
    }
    /**
     * 获取 redis 中hash类型的值
     * @author lichenglong
     * @param  string $key   hash 表名
     * @param  string $field key值
     * @return mixed         返回值
     */
    public static function getRedisHashValue($key = '', $field = '')
    {
        $redis = new self();
        $hKeyExist = $redis->isKeyExist($key, $field);
        if ($hKeyExist) {
            $value = $redis->getValueFromHashTable($key, $field);
        } else {
            $value = false;
        }
        $redis->close();
        return $value;
    }
    /**
     * 设置 redis 中hash类型的值
     * @param string  $key    hash表名
     * @param string  $field  键
     * @param string  $value  值
     * @param integer $expire 过期时间<秒>
     *
     */
    public static function setRedisHashValue($key = '', $field = '', $value = '', $expire = 1)
    {
        $redis = new self();
        $isOK = $redis->setAdvHash($key, $field, $value, $expire);
        $redis->close();
        return $isOK;
    }

    /**
     * 删除 redis 中hash类型的值
     * @param string  $key    hash表名
     * @param string  $field  键
     * @return bool
     */
    public static function delRedisHashValue($key = '', $field = '')
    {
        $redis = new self();
        $redis->removeHashTableField($key, $field);
        $redis->close();
        return true;
    }
}
