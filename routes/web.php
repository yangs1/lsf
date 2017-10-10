<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-27
 * Time: 下午10:42
 */

$app->router->group(['domain'=>'fccn.cc', 'namespace' => 'App\Http\Controllers','middleware'=>['auth:a','session'],'version'=>"v1"], function () use($app) {
    //$app->router->get('/', 'ExampleControllers@index');
    $app->router->get('/', 'ExampleControllers@index');//->where('name', '[A-Za-z]+');
  /*  $app->router->get('/task', 'ExampleControllers@taskDemo');
    $app->router->post('/cookie', 'ExampleControllers@CookieDemo');
    $app->router->post('/validate', 'ExampleControllers@validateDemo');
    $app->router->post('/event', 'ExampleControllers@eventDemo');*/
});