<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-9
 * Time: 下午11:21
 */

return [

    "listen"=>"0.0.0.0",
    "server_name"=>"",
    "port"=>9501,
    "wss"=>false,

    'settings' => [
        //"daemonize"=> true,
        'task_worker_num' => 0, //异步任务进程
        "task_max_request"=>10,
        'max_request'=>3000,
        'worker_num'=> 1,
        "log_file"=>base_path('storage/swoole.log'),
        'pid_file'=>base_path('pid.pid'),

    ]
];