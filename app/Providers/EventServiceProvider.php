<?php

namespace App\Providers;

use App\Events\Event;
use App\Events\Listener;
use Illuminate\Support\ServiceProvider;


class EventServiceProvider extends ServiceProvider
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
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [];

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        $events = app('events');

        foreach ($this->listen as $event => $listeners) {
            foreach ((array)$listeners as $listener) {
                $events->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            $events->subscribe($subscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        //
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }
}
