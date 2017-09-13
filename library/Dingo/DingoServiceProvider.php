<?php

/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-6
 * Time: 下午10:06
 */
namespace Library\Dingo;

use RuntimeException;
use ReflectionClass;
use Dingo\Api\Provider\ApiServiceProvider;
use FastRoute\RouteParser\Std as StdRouteParser;
use FastRoute\DataGenerator\GroupCountBased as GcbDataGenerator;

class DingoServiceProvider extends ApiServiceProvider
{

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->setupConfig();
        $this->setupClassAliases();

        $this->registerExceptionHandler();
       // $this->registerDispatcher();
        //$this->registerAuth();
        //$this->registerRateLimiting();

        //$this->registerUrlGenerator();
        $this->registerRouter();
        $this->registerAdapter();
        $this->registerHttpValidation();
        $this->registerMiddleware();

        $this->registerResponseFactory();
        $this->registerTransformer();

        //$this->registerDocsCommand();


    }

    public function registerAdapter(){
        $reflection = new ReflectionClass($this->app);

        $this->app->instance('app.middleware', $this->gatherAppMiddleware($reflection));

        $this->addRequestMiddlewareToBeginning($reflection);

        $this->app->singleton('api.router.adapter', function ($app) {
            return new DingoAdapter($app, new StdRouteParser, new GcbDataGenerator, 'FastRoute\Dispatcher\GroupCountBased');
        });
    }

    /**
     * Add the request middleware to the beginning of the middleware stack on the
     * Lumen application instance.
     *
     * @param \ReflectionClass $reflection
     *
     * @return void
     */
    protected function addRequestMiddlewareToBeginning(ReflectionClass $reflection)
    {
        $property = $reflection->getProperty('middleware');
        $property->setAccessible(true);

        $middleware = $property->getValue($this->app);

        array_unshift($middleware, 'Dingo\Api\Http\Middleware\Request');

        $property->setValue($this->app, $middleware);
        $property->setAccessible(false);
    }

    /**
     * Gather the application middleware besides this one so that we can send
     * our request through them, exactly how the developer wanted.
     *
     * @param \ReflectionClass $reflection
     *
     * @return array
     */
    protected function gatherAppMiddleware(ReflectionClass $reflection)
    {
        $property = $reflection->getProperty('middleware');
        $property->setAccessible(true);

        $middleware = $property->getValue($this->app);

        return $middleware;
    }
    /**
     * Setup the configuration.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $this->app->configure("api");

        $config = isset($this->app['config']['api']) ? $this->app['config']['api'] : null;
        if (!$config || (empty($config['prefix']) && empty($config['domain']))) {
            throw new RuntimeException('Unable to boot ApiServiceProvider, configure an API domain or prefix.');
        }
    }



}