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
    | max_request 设置多少次执行之后，会重启子进程，防止内存泄漏
    |
    */
    'options' => [
        'user' => env('SERVER_USER'),
        'group' => env('SERVER_GROUP'),
        'daemonize' => env('SERVER_DAEMONIZE', false),
        'worker_num' => env('SERVER_WORKER_NUM', 4),
        'max_request' => env('SERVER_MAX_REQUEST', 2000),
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
