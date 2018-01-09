<?php

class Response{
    public function __construct()
    {
        
    }
    public function json($array = [], $repcode = 200)
    {
        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        header('Content-Type: application/json');
        if ($repcode == 404) {
            header("HTTP/1.1 404 Not Found");  
            header("Status: 404 Not Found");
        }
        if (is_array($array)) {
            echo json_encode($array);exit;
        } else {
            echo $array;exit;
        }
    }
    public function output($string = '', $repcode = 200, $isJs = 0)
    {
        if ($isJs) {
            header('Content-Type: text/javascript');
        } else {
            header('Content-Type: text/html');
        }
        if ($repcode == 404) {
            header("HTTP/1.1 404 Not Found");  
            header("Status: 404 Not Found");
        }
        echo $string;
        exit;
    }
}



