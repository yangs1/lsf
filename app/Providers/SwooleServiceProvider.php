<?php

/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-7
 * Time: 下午10:23
 */
namespace  App\Providers;

use Illuminate\Support\ServiceProvider;
use Library\Swoole\SwooleHttpServer;

class SwooleServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(){
        $this->app->singleton("swoole", function (){
            return new SwooleHttpServer($this->app, $this->app['config']->get("swoole"));
        });
    }

    public function boot(){

    }

}