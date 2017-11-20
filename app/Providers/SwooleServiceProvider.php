<?php

/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-7
 * Time: 下午10:23
 */
namespace  App\Providers;

use Illuminate\Support\ServiceProvider;
use League\Flysystem\Exception;
use Foundation\Swoole\SwooleHttpServer;

class SwooleServiceProvider extends ServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        "swoole.beforeStart" => "App\\Events\\SwooleEvents@beforeStart",
        "swoole.start" => "App\\Events\\SwooleEvents@onStart",
        "swoole.shutdown" => "App\\Events\\SwooleEvents@onShutdown",
        "swoole.workerStart" => "App\\Events\\SwooleEvents@onWorkerStart",
        "swoole.workerStop" => "App\\Events\\SwooleEvents@onWorkerStop",
        "swoole.workererror" => "App\\Events\\SwooleEvents@onWorkerError",
        //"swoole.request" => "App\\Events\\SwooleEvents@onRequest",
        "swoole.task" => "App\\Events\\SwooleEvents@onTask",
        "swoole.finish" => "App\\Events\\SwooleEvents@onFinish",

        "swoole.message" => "App\\Events\\SwooleEvents@onMessage",
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(){
        if (!$this->app->bound('events')){
            $this->app->register('Illuminate\Events\EventServiceProvider');
        }
    }

    public function boot(){
        $events = $this->app['events'];
        foreach ($this->listen as $event => $listeners) {
            foreach ((array)$listeners as $listener) {
                $events->listen($event, $listener);
            }

        }
        return new SwooleHttpServer($this->app);
    }

}