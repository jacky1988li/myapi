<?php

function loadprint($class)
{
    $file = false;
    if (strpos($class, 'Controller') > 0) {
        $file = ROOT_DIR.'/controllers/' . $class . PHP_EXT;
    } else {
        $files = [
            ROOT_DIR . '/libraries/' . $class . '.class' . PHP_EXT,
            ROOT_DIR . '/libraries/' . $class . PHP_EXT,
            ROOT_DIR . '/tool/' . $class . '.class' . PHP_EXT,
            ROOT_DIR . '/tool/' . $class . PHP_EXT,
        ];
        foreach ($files as $_file) {
            if (is_file($_file)) {
                $file = $_file;
                break;
            }
        }
    }
    // echo $file, PHP_EOL;
    if (is_file($file)) {
        require_once($file);
    }
}

function config($string)
{
    $configs = explode('.', $string);
    $file = ROOT_DIR .'/config/' . $configs[0] . PHP_EXT;
    $len = count($configs);
    $ret = require($file);
    for ($i = 1; $i < $len; $i++) {
        $ret = $ret[$configs[$i]];
    }
    return $ret;
}

/**
 * 驼峰转下划线
 * @param string $str
 * @return string
 */
function snake_case($str)
{
    return preg_replace_callback('/([A-Z]{1})/', function($matches){
        return '_'.strtolower($matches[0]);
    },$str);
}

function is_json($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function getFakeImsi()
{
    return '4600' . mt_rand(1,3) . mt_rand(10000000000, 9999999999999) . chr(mt_rand(65, 90));
}

function getFakeMacAddress()
{
    $list = [];
    for ($i = 0; $i < 6; $i++) {
        $list[] = dechex(mt_rand(16,255));
    }
    return implode(':', $list);
}

// 获取一个伪造的公网ip地址
function getFakeIP()
{
    return '101.' . mt_rand(4,95) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255);
}

/**
 * 通过手机ua获取手机操作系统类型以及手机品牌
 * @param string $ua
 * @return array
 */
function get_mobile_ua_extra($ua)
{
    // os type[0:unknown, 1:android, 2:ios, 3:wp]
    $osType = 1;
    $brandName = 'OPPO';
    $osVersion = '6.0';
    if (strpos($ua, 'Linux; Android')>0) {
        $osType = 1;
        preg_match('/Linux; Android (.*?);/', $ua, $match);
        $osVersion = str_replace('_', '.', $match[1]);
    }
    if (strpos($ua, 'iPhone; CPU iPhone OS')>0 || strpos($ua, 'iPad; CPU OS')>0) {
        $osType = 2;
        $brandName = 'APPLE';
        preg_match('/OS (.*?) like Mac OS X/', $ua, $match);
        $osVersion = str_replace('_', '.', $match[1]);
    }
    if (strpos($ua, 'Windows Phone')>0) {
        $osType = 3;
        preg_match('/Windows Phone (.*?); Android/', $ua, $match);
        $osVersion = $match[1];
    }
    if (stripos($ua, 'HUAWEI')>0 && stripos($ua, 'BUILD')>0) {
        $brandName = 'HUAWEI';
    }
    if (stripos($ua, 'OPPO')>0 && stripos($ua, 'BUILD')>0) {
        $brandName = 'OPPO';
    }
    if (stripos($ua, 'vivo')>0 && stripos($ua, 'BUILD')>0) {
        $brandName = 'vivo';
    }
    if ((stripos($ua, 'SM-')>0 || stripos($ua, 'GT-')>0 || stripos($ua, 'SCH-')>0)&& stripos($ua, 'BUILD')>0) {
        $brandName = 'SAMSUNG';
    }
    if ((stripos($ua, 'MI')>0 || stripos($ua, 'HM')>0 )&& stripos($ua, 'BUILD')>0) {
        $brandName = 'MI';
    }
    if (stripos($ua, 'Coolpad')>0 && stripos($ua, 'BUILD')>0) {
        $brandName = 'coolpad';
    }

    $isSymbian = preg_match('/Windows Phone/', $ua) || preg_match('/SymbianOS/', $ua);
    $isAndroid = preg_match('/Android/', $ua);
    $isTablet = preg_match('/iPad/', $ua) || preg_match('/PlayBook/', $ua) || ($isAndroid && !preg_match('/Mobile/', $ua)) || (preg_match('/Firefox/', $ua) && preg_match('/Tablet/', $ua));
    $isPhone = preg_match('/iPhone/', $ua) && !$isTablet;
    $isPc = !$isPhone && !$isAndroid && !$isSymbian;
    if ($isTablet) {
        $deviceType = 2;
    } elseif ($isPc) {
        $deviceType = 0;
    } else {
        $deviceType = 1;
    }
    return ['osType' => $osType, 'brand' => $brandName, 'osVersion' => $osVersion, 'deviceType' => $deviceType];
}

function is_mobile_no($number)
{
    return preg_match('/^1[3456789]{1}\d{9}$/', $number);
}

function Get_Ip()
{
    $onlineip = '';
    if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }
    return $onlineip;
}

function _input($input)
{
    return (isset($input) && $input) ? $input : '';
}

function strtodate($datetime)
{
    return substr($datetime, 0, 10);
}

//curl  post请求发送验证短信
function sendCurlPost($url, $data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    $ret = curl_exec($curl);
    curl_close($curl);
    return $ret;
}

function getBaiduChannelLink($catid)
{
    return 'https://cpu.baidu.com/'. $catid .'/d7d2f5e0';
}


/**
 * 获取客户端IP的地址
 * 参考 https://segmentfault.com/q/1010000000686700
 *
 * @return mixed
 */
function get_client_ip()
{
    foreach (array(
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER)) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                //会过滤掉保留地址和私有地址段的IP，例如 127.0.0.1会被过滤
                //也可以修改成正则验证IP
                if ((bool) filter_var($ip, FILTER_VALIDATE_IP,
                                FILTER_FLAG_IPV4 |
                                FILTER_FLAG_NO_PRIV_RANGE |
                                FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
    }
    return null;
}

/**
 * 功能：判断Email是否合法
 * @param   $email          string     email字符串
 * @return  bool          true/false
 * @author  wxp           2016/12/27
 */
function is_email($email)
{
     return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/",$email);
}

/**
 * 功能：构建url查询字符串
 * @param   array       $param
 * @return  string
 */
function build_http_query($param)
{
    $qs = [];
    foreach ($param as $key => $val) {
        $qs[] = "$key=$val";
    }
    return implode('&', $qs);
}

function sendCurlGet($url)
{
    //初始化
    $ch = curl_init();
    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, config('app.curl_timeout_ms'));
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    return $output;
}

function getCurrentTime()
{
    return date('Y-m-d H:i:s');
}

function getCurrentDate()
{
    return date('Y-m-d');
}


