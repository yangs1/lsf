<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-9
 * Time: 下午11:21
 */

return [
    'key'           => 'base64:JdpjOMcZODAky9BnkUsWPI1JOweqHY3PhqMIrhjWutE=',
    'cipher'        => 'AES-256-CBC', // AES-128-CBC , AES-256-CBC

    'app_debug'     =>  true,
    'app_model'     =>  "api", //http, api
    'locale'        => 'zh_cn', //zh_cn, en
    'fallback_locale'   => 'en',

    'log'           =>  'daily',
    'log_max_files' =>  3,
    'log_level'     =>  "debug", //debug , info , notice , warning , error , critical , alert , emergency
    'log_name'      =>  "yf",
    'log_channel'   =>  "LOG",


    /*
     | 一共提供了3 种 tree,x, prs, vnd
     | x:   unregistered tree 本地或是私有环境
     | prs: personal Tree 项目不是用于商业发布的
     | vnd: vender tree 用于公开的商业项目
     */

    'standardsTree' => 'x',

    /*
     * 项目或工程的简称，全部小写
     */
    'subtype' => 'project',

    /*
     * 默认版本号
     */
    'version' => 'v1',

    /*
     * 默认域名，与前缀二选一
     */
    //'domain' => null,

    /*
     * 是否开启严格模式
     */
    'strict' => true,

    'defaultFormat' => 'json',

    'formats' => [
        'message' => ':message',
        'errors' => ':errors',
        'status_code' => ':status_code',
        'code' => ':code',
        'debug' => ':debug',
    ]
];