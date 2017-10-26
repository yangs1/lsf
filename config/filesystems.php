<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => base_path('storage/temp'),
        ],

        's3' => [
            'driver' => 's3',
            'key' => '',
            'secret' => '',
            'region' => '',
            'bucket' => '',
        ],

        /*'qiniu' => [
            'driver'  => 'qiniu',
            'domains' => [
                'default'   => 'owzwcvuy2.bkt.clouddn.com', //你的七牛域名
                'https'     => '127.0.0.1',         //你的HTTPS域名
                'custom'    => 'static.abc.com',                //Useless 没啥用，请直接使用上面的 default 项
            ],
            'access_key'=> 'qWfUbOxhFQnEQaAr5cvtOFCUZ-Cvtj_KQIYPWNyl',  //AccessKey
            'secret_key'=> 'n9hzlaTvNTohFLi5QDFG9ShxfAvNIVGasbOAnPZf',  //SecretKey
            'bucket'    => 'kzae',  //Bucket名字
            'notify_url'=> '',  //持久化处理回调地址
            'access'    => 'public'  //空间访问控制 public 或 private
        ],*/
        'qiniu' => [
            'driver'     => 'qiniu',
            'access_key' => 'qWfUbOxhFQnEQaAr5cvtOFCUZ-Cvtj_KQIYPWNyl',
            'secret_key' => 'n9hzlaTvNTohFLi5QDFG9ShxfAvNIVGasbOAnPZf',
            'bucket'     => 'kzae',
            'domain'     => 'owzwcvuy2.bkt.clouddn.com', // or host: https://xxxx.clouddn.com
        ],

    ],

];
