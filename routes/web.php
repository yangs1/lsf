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
$api = app('api.router');
$api->version('v1', function ($api){
    $api->get('/', function (\Illuminate\Http\Request $request) {
       // var_dump($request->fd);
       //var_dump(debug_backtrace());
       //var_dump($request->getOrigin());
        //throw new Exception("aaa");
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