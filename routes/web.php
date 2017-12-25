<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-27
 * Time: 下午10:42
 */

$app->router->group([ 'namespace' => 'App\Http\Controllers','version'=>"v1", "middleware"=>['session','cookie']], function () use($app) {
    //$app->router->get('/', 'ExampleControllers@index');
    $app->router->get('/', 'ExampleControllers@index');//->where('name', '[A-Za-z]+');
    $app->router->get('/task', 'ExampleControllers@taskDemo');
   /* $app->router->post('/cookie', 'ExampleControllers@CookieDemo');
    $app->router->post('/validate', 'ExampleControllers@validateDemo');
    $app->router->post('/event', 'ExampleControllers@eventDemo');*/
    $app->router->post('/validate', 'ExampleControllers@validateDemo');

    $app->router->get('/test', 'RegisterController@register');
    $app->router->get('/check', 'RegisterController@check');
});//'domain'=>'fccn.cc','middleware'=>['auth:a','session']