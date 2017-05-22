<?php

return [
    'host' => '127.0.0.1',
    'port' => '9058',

    /*
    |--------------------------------------------------------------------------
    | server config 
    |--------------------------------------------------------------------------
    |
    | 此处配置为swoole_serverd的配置选项, 可根据实际
    |
    */
    'options' => [
        'user' => env('SERVER_USER'),
        'group' => env('SERVER_GROUP'),
        'daemonize' => env('SERVER_DAEMONIZE', false),
        'worker_num' => env('SERVER_WORKER_NUM', 4)
    ],

    /*
    |--------------------------------------------------------------------------
    | worker start include 
    |--------------------------------------------------------------------------
    |
    | worker 启动时需要include的文件, 默认加载路径为 bootstrap
    |
    */    
    'worker_start_include' => [
        'route.php',
    ],
];