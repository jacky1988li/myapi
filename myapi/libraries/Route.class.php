<?php

class Route{
    /**
     * @param $uri
     * @param $params
     * @return bool
     */
    public static function post($uri, $params)
    {
        $realUrl = ltrim($_SERVER['REQUEST_URI'], '/');
        if ($realUrl != $uri) {
            // echo $realUrl, '#',$uri , PHP_EOL;
            return true;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        $request = new Request();
        $response = new Response();
        
        if (isset($params['middleware'])) {
            $middleware = ucfirst($params['middleware']);
            $middleObj = new $middleware();
            $middleObj->handle($request, $response);
        }
        
        $uses = $params['uses'];

        list($controller, $method) = explode('@', $uses);
        $ctrlFile = ROOT_DIR . '/controllers/' . $controller . PHP_EXT;
        if (file_exists($ctrlFile)){
            if (class_exists($controller) && method_exists($controller, $method)) {
                $app = new $controller();
                $app->$method($request, $response);
            } else {
                echo 'class or method not exists';exit;
            }
        } else {
            echo "controller not exists"; exit;
        }
    }

    /**
     * @param $uri
     * @param $params
     * @return bool
     */
    public static function get($uri, $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return true;
        }
        $realUrl = ltrim($_SERVER['REQUEST_URI'], '/');
        $pattern = '#' . $uri . '#';
        
        if (preg_match($pattern, $realUrl, $match)) {
            if (isset($params['callback'])) {
                call_user_func($params['callback'], $match);
            } else {
                
            }
        } else {
            if (ltrim($_SERVER['REQUEST_URI'], '/')!== $uri) {
                return true;
            }
        }
        
        if (isset($params['uses'])) {
            $uses = $params['uses'];
            list($controller, $method) = explode('@', $uses);
            $ctrlFile = ROOT_DIR . '/controllers/' . $controller . PHP_EXT;
            if (file_exists($ctrlFile)){
                if (class_exists($controller) && method_exists($controller, $method)) {
                    $app = new $controller();
                    $request = new Request();
                    $response = new Response();
                    $app->$method($request, $response);
                } else {
                     echo 'class or method not exists'; exit;
                }
            } else {
                echo "controller not exists"; exit;
            }
        }
    }
}



