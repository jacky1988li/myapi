<?php


if (ONLINE) {
    return [
        'DB_HOST' => '10.168.10.163',
        'DB_PORT' => 3306,
        'DB_DATABASE' => 'infofloxwx',
        'DB_USERNAME' => 'infofloxwx',
        'DB_PASSWORD' => 'i8Te_!n8&xZ',
        'DB_DSN' => 'mysql:host=10.168.10.163;port=3306;dbname=infofloxwx',
        'DB_ENGINE_DATABASE' => 'infoflowengine',

        'REDIS_HOST' => '10.168.10.102',
        'REDIS_PORT' => 6379,
        'REDIS_PASS' => 'scrs-kcvidvn20:wxcm2017',
    ];
} else {
    return [
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => 3306,
        'DB_DATABASE' => 'infoflow',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => 'Wangxiang2016',
        'DB_DSN' => 'mysql:host=127.0.0.1;port=3306;dbname=infoflow',
        'DB_ENGINE_DATABASE' => 'infoflowengine',

        'REDIS_HOST' => '127.0.0.1',
        'REDIS_PORT' => 6379,
        'REDIS_PASS' => '',
    ];
}


