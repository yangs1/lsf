<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-8
 * Time: 上午9:55
 */
require 'vendor/autoload.php';


$app = new \App\Application();

/*$app->router->get('/', function (\Dingo\Api\Http\Request $request) {
    //var_dump(debug_backtrace());
    throw  new Exception("aaa");
});*/

$app->router->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__.'/routes/web.php';
});

$app->swoole->start();