<?php
    /**
     * Created by PhpStorm.
     * User: yang
     * Date: 17-8-8
     * Time: 上午9:55
     */
    require 'vendor/autoload.php';

    $app = new \Foundation\Application();

    $app->withFacades();
    $app->withEloquent();
    //$app->middleware()
    $app->routeMiddleware([
        "auth"      =>\App\Http\Middleware\AuthMiddleware::class
    ]);
    $app->middleware([
        \Foundation\Session\StartSession::class,
        \App\Http\Middleware\AuthMiddleware::class,
        \Foundation\Cookie\Middleware\AddQueuedCookiesToResponse::class
    ]);
    $app->register(Overtrue\LaravelFilesystem\Qiniu\QiniuStorageServiceProvider::class);
    $app->router->group([ 'namespace' => 'App\Http\Controllers'], function () use($app) {

        $app->router->get('/', 'ExampleControllers@index');
        /*$app->router->get('/', 'ExampleControllers@validator');
        $app->router->get('/', 'ExampleControllers@CookieDemo');
        $app->router->get('/', 'ExampleControllers@eventDemo');

        $app->router->get('/', 'ExampleControllers@SessionDemo');
        $app->router->get('/', 'ExampleControllers@CacheDemo');
        $app->router->post('/', 'ExampleControllers@FilesDemo');
        $app->router->get('/', 'ExampleControllers@encryptDemo');
        $app->router->get('/', 'ExampleControllers@dbDemo');
        $app->router->get('/', 'ExampleControllers@taskDemo');*/
    });


    if(function_exists('apc_clear_cache')){
        apc_clear_cache();
    }
    if(function_exists('opcache_reset')){
        opcache_reset();
    }

    //var_dump($app->router);
    //$app->parse_command();
$app->register( \App\Providers\ConsoleServiceProvider::class );