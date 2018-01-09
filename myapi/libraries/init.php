<?php

date_default_timezone_set('Asia/Shanghai');

include_once ROOT_DIR . '/libraries/function.php';
// 自动加载
spl_autoload_register( 'loadprint' );


define('ONLINE', trim(file_get_contents('.env')) == 'production');

/*数据库连接*/
define('DBNAME', config('database.DB_DATABASE'));
define('DBENGINE_NAME', config('database.DB_ENGINE_DATABASE'));

define('DB_DSN', config('database.DB_DSN'));
define('DB_USER', config('database.DB_USERNAME'));
define('DB_PASSWD', config('database.DB_PASSWORD'));

define('REDIS_HOST', config('database.REDIS_HOST'));
define('REDIS_PORT', config('database.REDIS_PORT'));
define('REDIS_PASS', config('database.REDIS_PASS'));

define('TBL_PREFIX', 't_');

// 路由分配
include_once ROOT_DIR . '/config/routes.php';

// 没有匹配的路由则终止
(new Response)->json(['code' => 404, 'msg' => '没有匹配的链接'], 404);
