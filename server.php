<?php
    /**
     * Created by PhpStorm.
     * User: yang
     * Date: 17-8-8
     * Time: 上午9:55
     */
    require 'vendor/autoload.php';

    $app = new \Foundation\Application();

    $app->withFacades(true);

    $app->withEloquent();

    //$app->middleware()
    $app->routeMiddleware([
        "session"   => \Foundation\Session\StartSession::class,
        "cookie"    => \Foundation\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        "auth"      =>\App\Http\Middleware\AuthMiddleware::class
    ]);

   // $app->register(Overtrue\LaravelFilesystem\Qiniu\QiniuStorageServiceProvider::class);

    require __DIR__.'/routes/web.php';


    if(function_exists('apc_clear_cache')){
        apc_clear_cache();
    }
    if(function_exists('opcache_reset')){
        opcache_reset();
    }

    //var_dump($app->router);
    $app->parse_command();