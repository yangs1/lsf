<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-8
 * Time: 上午9:55
 */
require 'vendor/autoload.php';

$app = new \Library\Application();

$app->routeMiddleware([
   "auth" =>\App\Http\Middleware\AuthMiddleware::class
]);


$app->withFacades(true);

$app->withEloquent();



require __DIR__.'/routes/web.php';


if(function_exists('apc_clear_cache')){
    apc_clear_cache();
}
if(function_exists('opcache_reset')){
    opcache_reset();
}

$app->parse_command();