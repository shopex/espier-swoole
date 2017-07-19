<?php

return [
    'host' => env('SERVER_HOST', '0.0.0.0'),
    'port' => env('SERVER_PORT', '9058'),

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