<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-8
 * Time: 上午9:55
 */
require 'vendor/autoload.php';

$app = new \App\Application();

$app->routeMiddleware([
   "auth" =>\App\Http\Middleware\AuthMiddleware::class
]);
//$app->withFacades();
/*$app->router->get('/', function (\Illuminate\Http\Request $request) {
    //var_dump(debug_backtrace());
   // throw  new Exception("aaa");
    return new \Illuminate\Http\Response(["666"]);
});*/

$app->router->group(['namespace' => 'App\Http\Controllers','middleware'=>"auth"], function () use($app) {
    $app->router->get('/', 'TestControllers@index');
    $app->router->get('/t', 'TestControllers@test2');
});

//require __DIR__.'/routes/web.php';

if(function_exists('apc_clear_cache')){
    apc_clear_cache();
}
if(function_exists('opcache_reset')){
    opcache_reset();
}

$app->parse_command();

/*$s = new \SuperClosure\Serializer();
$a = function ($name = "a"){
    echo "666";
};
$b = $s->serialize($a);
$c = $s->unserialize($b);
$c();*/
//var_dump($b);
