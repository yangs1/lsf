<?php

namespace Foundation\Queue;

use Foundation\Queue\Connectors\SwooleConnector;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Foundation\Queue\Connectors\NullConnector;
use Foundation\Queue\Connectors\SyncConnector;
use Foundation\Queue\Connectors\RedisConnector;
use Foundation\Queue\Failed\NullFailedJobProvider;
use Foundation\Queue\Failed\DatabaseFailedJobProvider;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerManager();

        $this->registerWorker();

        $this->registerFailedJobServices();

        $this->registerQueueClosure();
    }

    /**
     * Register the queue manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('queue', function ($app) {
            // Once we have an instance of the queue manager, we will register the various
            // resolvers for the queue connectors. These connectors are responsible for
            // creating the classes that accept queue configs and instantiate queues.
            $manager = new QueueManager($app);

            $this->registerConnectors($manager);

            return $manager;
        });
    }

    /**
     * Register the queue worker.
     *
     * @return void
     */
    protected function registerWorker()
    {
        $this->app->singleton('queue.worker', function ($app) {
            return new SwooleWorker(
                $this->app['queue'], $this->app['events'], $this->app['Illuminate\Contracts\Debug\ExceptionHandler']
            );
        });
    }


    /**
     * Register the connectors on the queue manager.
     *
     * @param  \Foundation\Queue\QueueManager  $manager
     * @return void
     */
    public function registerConnectors($manager)
    {
        foreach (['Null', 'Sync', 'Redis'] as $connector) {
            $this->{"register{$connector}Connector"}($manager);
        }
    }

    /**
     * Register the Null queue connector.
     *
     * @param  \Foundation\Queue\QueueManager  $manager
     * @return void
     */
    protected function registerNullConnector($manager)
    {
        $manager->addConnector('null', function () {
            return new NullConnector;
        });
    }

    /**
     * Register the Sync queue connector.
     *
     * @param  \Foundation\Queue\QueueManager  $manager
     * @return void
     */
    protected function registerSyncConnector($manager)
    {
        $manager->addConnector('sync', function () {
            return new SyncConnector;
        });
    }

    /**
     * Register the Redis queue connector.
     *
     * @param  \Foundation\Queue\QueueManager  $manager
     * @return void
     */
    protected function registerRedisConnector($manager)
    {
        $app = $this->app;

        $manager->addConnector('redis', function () use ($app) {
            return new RedisConnector($app['redis']);
        });
    }

    /**
     * Register the failed job services.
     *
     * @return void
     */
    protected function registerFailedJobServices()
    {
        $this->app->singleton('queue.failer', function ($app) {
            $config = $app['config']['queue.failed'];

            if (isset($config['table'])) {
                return new DatabaseFailedJobProvider($app['db'], $config['database'], $config['table']);
            } else {
                return new NullFailedJobProvider;
            }
        });
    }

    /**
     * Register the Illuminate queued closure job.
     *
     * @return void
     */
    protected function registerQueueClosure()
    {
        $this->app->singleton('IlluminateQueueClosure', function ($app) {
            return new IlluminateQueueClosure($app['encrypter']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'queue', 'queue.worker', 'queue.listener', 'queue.failer',
            'command.queue.work', 'command.queue.listen', 'command.queue.restart',
            'command.queue.subscribe',
        ];
    }
}
