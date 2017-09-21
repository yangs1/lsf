<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-8
 * Time: 上午9:55
 */
require 'vendor/autoload.php';

/*======本版本主要速度限制在于 Request Response 的重新 =========*/
$app = new \App\Application();

/*$app->router->get('/', function (\Illuminate\Http\Request $request) {
    //var_dump(debug_backtrace());
   // throw  new Exception("aaa");
    return new \Illuminate\Http\Response(["666"]);
});*/

/*$app->router->group(['namespace' => 'App\Http\Controllers'], function ($app) {

});*/

require __DIR__.'/routes/web.php';

if(function_exists('apc_clear_cache')){
    apc_clear_cache();
}
if(function_exists('opcache_reset')){
    opcache_reset();
}

$app->parse_command();

//$app->swoole->start();


/*$s = new \SuperClosure\Serializer();
$a = function ($name = "a"){
    echo "666";
};
$b = $s->serialize($a);
$c = $s->unserialize($b);
$c();*/
//var_dump($b);
