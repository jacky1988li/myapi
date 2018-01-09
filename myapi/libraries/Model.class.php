<?php

class Model
{
    public static $dbname = DBNAME;
    public static function setDbName($dbname = '')
    {
        if (in_array($dbname, [DBNAME, DBENGINE_NAME])) {
            self::$dbname = $dbname;
        }
    }

    public static function getData($tablename = '', $where = [], $page = 1, $limit = 10, $fields = '*', $orderBy = 'f_id desc')
    {
        if (self::$dbname == DBENGINE_NAME) {
            $obj = (new DB)->usedb(self::$dbname)->table($tablename);
        } else {
            $obj = (new DB)->table($tablename);
        }

        $data = $obj->where($where)
                    ->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->orderBy($orderBy)
                    ->get($fields);
        return $data;
    }

    public static function getFirstData($tablename = '', $where = [], $fields = '*')
    {
        if (self::$dbname == DBENGINE_NAME) {
            $data = (new DB)->usedb(self::$dbname)->table($tablename)
                ->where($where)->first($fields);
        } else {
            $data = (new DB)->table($tablename)->where($where)->first($fields);
        }
        return $data;
    }
    /**
     * 新增数据【最基础的新增方法】
     * @param  string  $tablename  
     * @param  array  $data    
     * @return int  
     */
    public static function saveData($tablename = '', $data = [])
    {
        if (self::$dbname == DBENGINE_NAME) {
            $a = (new DB)->usedb(self::$dbname)->table($tablename)->save($data);
        } else {
            $a = (new DB)->table($tablename)->save($data);
        }
        return $a;
    }
    /**
     * 更新数据【最基础的更新方法】
     * @param  string  $tablename  
     * @param  array $where
     * @param  array  $update    
     * @return bool  
     */
    public static function updateData($tablename = '', $where = [], $update = [])
    {
        if (self::$dbname == DBENGINE_NAME) {
            $a = (new DB)->usedb(self::$dbname)->table($tablename)->where($where)->update($update);
        } else {
            $a = (new DB)->table($tablename)->where($where)->update($update);
        }
        return $a;
    }

    public static function updateDataById($tablename = '', $id = 0, $update = [])
    {
        return self::updateData($tablename, ['f_id' => $id], $update);
    }
    /**
     * 更新用户数据
     * @param  array  $where        
     * @param  array  $update    
     * @return bool  
     */
    public static function updateUser($where = [], $update = [])
    {
        return self::updateData('users', $where, $update);
    }
    /**
     * 更新用户数据
     * @param  integer $id        
     * @param  array  $update    
     * @return bool  
     */
    public static function updateUserById($id = 0, $update = [])
    {
        return self::updateUser(['f_id' => $id], $update);
    }


    /**
     * 获取数据 【最基础的查询方法】
     * @param  string  $tablename  表名
     * @param  integer $id         id
     * @param  string  $fields     字段
     * @return object|array               结果对象/二维数组
     */
    public static function getDataById($tablename = '', $id = 0, $fields = '*')
    {
        //$cacheKey = 'feeds_'. $dbname. '_'. $tablename . '_' . $id;
        if (is_array($id)) {
            $data = [];
            foreach ($id as $val) {
               $data[$val] = self::getDataById( $tablename, $val, $fields);
            }
        } else {
            if (self::$dbname == DBENGINE_NAME) {
                $data = (new DB)->usedb(self::$dbname)->table($tablename)
                        ->where('f_id', $id)->first($fields);
            } else {
                $data = (new DB)->table($tablename)->where('f_id', $id)->first($fields);
            }
        }
        return $data;
    }
    /**
     * 获取作者信息
     * @param  integer $id 
     * @return object
     */
    public static function getAuthorById($id = 0)
    {
        return self::getDataById('authors', $id);
    }
    /**
     * 获取推送消息
     * @param  integer $id 
     * @return object
     */
    public static function getMessageById($id = 0)
    {
        return self::getDataById('push_messages', $id);
    }
    /**
     * 获取用户信息
     * @param  integer $id 
     * @return object
     */
    public static function getUserById($id = 0)
    {
        return self::getDataById('users', $id);
    }
    
}
