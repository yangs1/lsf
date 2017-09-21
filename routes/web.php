<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
use Dingo\Api\Routing\Helpers;
$api = app('api.router');
$api->version('v1', function ($api){
    //$api->get('/', 'App\Http\Controllers\TestControllers@index');
    $api->group(['middleware' => 'auth','prefix' => '', 'namespace'=>'App\Http\Controllers'], function () use ($api){
        $api->get('/', 'TestControllers@index');
    });
    $api->get('/a', function (\Illuminate\Http\Request $request) {
        /*=============================*/
       // var_dump($request->fd); //获取swoole
       //var_dump(debug_backtrace());  //php 执行的对象栈
       //var_dump($request->getOrigin()); //获取原始request

        /*==========================*/
        /*task(function (){  // swoole任务用法
            echo "ok ok ok !!!";
            return \App\Task\TestTask::class;
        },["a"=>2]);*/

        /*=============================*/
//var_dump((new \Dingo\Api\Http\Response(["666"]))->getTransformer());
        //app('Dingo\Api\Http\Response\Factory')

        return new \Dingo\Api\Http\Response(["666"]);
    });
    $api->get('/c', function (\Dingo\Api\Http\Request $request) {
        //var_dump(debug_backtrace());
       //var_dump(get_class(app("swoole")));
        return "ccc";
    });
});
$api->version('v2', function ($api){
    $api->get('/a', function (\Dingo\Api\Http\Request $request) {
        //var_dump(debug_backtrace());
        return "bbb";
    });
});
//var_dump($api);
/*$app->get('/', function () use ($app) {
    throw new Exception("aaaaaaaaaaa");
});*/
//$app->get('/test', "ExampleController@index");