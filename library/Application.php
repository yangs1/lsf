<?php

/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-9
 * Time: 上午9:38
 */
namespace Library;

use App\Exceptions\Handler;
use App\Providers\EventServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Container\Container;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Facade;
use Library\Concerns\RegistersConsole;
use Library\Concerns\RoutesRequests;
use Library\Dingo\DingoServiceProvider;
use Library\Log\LogServiceProvider;
use Illuminate\Support\ServiceProvider;
use Library\Concerns\RegistersExceptionHandlers;
use Library\Routing\Router;
use Library\Swoole\Task\TaskManager;

class Application extends Container{

    use RegistersExceptionHandlers, RoutesRequests, RegistersConsole;
    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The Router instance.
     *
     * @var \Dingo\Api\Routing\Router
     */
    public $router;
    /**
     * The Swoole instance.
     */
    public $swoole;
    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigurations = [];
    /**
     * The loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];
    /**
     * The service binding methods that have been executed.
     *
     * @var array
     */
    protected $ranServiceBinders = [];
    /**
     * A custom callback used to configure Monolog.
     *
     * @var callable|null
     */
    protected $monologConfigurator;
    /**
     * Indicates if the class aliases have been registered.
     *
     * @var bool
     */
    protected static $aliasesRegistered = false;

    public $availableBindings = [
        'config'    =>  'registerConfigBindings',
        'db'        =>  'registerDatabaseBindings',
        'events'    =>  'registerEventBindings',
        'log'       =>  'registerLogBindings',
        'files'     =>  'registerFilesBindings',
        'validator'=>'registerValidatorBindings',
        "translator"    =>"registerTranslationBindings",
        FilesystemManager::class => "registerFilesSystemBindings",
    ];

    protected $aliases = [
        'request' => 'Illuminate\Http\Request',
        'task'      => TaskManager::class,
        'Psr\Log\LoggerInterface' => 'log',
        'Illuminate\Contracts\Debug\ExceptionHandler'=> Handler::class
    ];
    /**
     * Create a new Lumen application instance.
     * @param null $basePath
     */
    public function __construct($basePath = null){
        date_default_timezone_set('Asia/Shanghai');
        $this->basePath = $basePath;

        $this->bootstrapContainer();
        $this->registerErrorHandling();
        $this->bootstrapRouter();
    }


    /**
     * Bootstrap the application container.
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance('path', $this->path());
    }

    /**
     * Bootstrap the router instance.
     *
     * @return void
     */
    public function bootstrapRouter()
    {
        if ($this->runningInModel() === "dingo"){
            $this->router = $this->loadComponent("app",DingoServiceProvider::class, 'api.router');
        }else if ($this->runningInModel() === "mix"){
            $this->router = new Router($this);
            $this->loadComponent("app",DingoServiceProvider::class);
        }else{
            $this->router = new Router($this);
        }
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }

    public function runningInModel()
    {
        $this->configure('app');
        return config('app.app_model', 'default');
    }

    /**
     * Resolve the given type from the container.
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (array_key_exists($abstract, $this->availableBindings) &&
            ! array_key_exists($this->availableBindings[$abstract], $this->ranServiceBinders)) {
            $this->{$method = $this->availableBindings[$abstract]}();

            $this->ranServiceBinders[$method] = true;
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Get the base path for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    public function basePath($path = null)
    {
        if (isset($this->basePath)) {
            return $this->basePath.($path ? '/'.$path : $path);
        }
        $this->basePath = getcwd();
        return $this->basePath($path);
    }
    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->basePath().DIRECTORY_SEPARATOR.'app';
    }

    /**
     * Configure and load the given component and provider.
     *
     * @param  string  $config
     * @param  array|string  $providers
     * @param  string|null  $return
     * @return mixed
     */
    public function loadComponent($config, $providers, $return = null)
    {
        $this->configure($config);
        foreach ((array) $providers as $provider) {
            $this->register($provider);
        }
        if ($return){
            return $this->make($return);
        }
        return null;
    }
    /**
     * Load a configuration file into the application.
     *
     * @param  string  $name
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }
        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param  string|null  $name
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (!$name) {
            $appConfigDir = $this->basePath('config').'/';
            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config').'/'.$name.'.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Get the path to the application's language files.
     *
     * @return string
     */
    protected function getLanguagePath()
    {
        if (is_dir($langPath = $this->basePath().'/resources/lang')) {
            return $langPath;
        } else {
            return __DIR__.'/../resources/lang';
        }
    }
    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider)
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return null;
        }

        $this->loadedProviders[$providerName] = true;

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
        return null;
    }

    protected function registerConfigBindings()
    {
        $this->singleton('config', ConfigRepository::class);
    }

    protected function registerDatabaseBindings()
    {
        $this->singleton('db', function () {
            return $this->loadComponent(
                'database',
                ['Illuminate\Database\DatabaseServiceProvider'],
                'db'
            );
        });
    }

    protected function registerFilesBindings()
    {
        $this->singleton('files', function () {
            return new Filesystem;
        });
    }

    protected function registerFilesSystemBindings()
    {
        $this->configure('filesystems');
        $this->singleton(FilesystemManager::class,function (){
            return new FilesystemManager(app());
        });
    }

    protected function registerEventBindings()
    {
       $this->loadComponent('events',['Illuminate\Events\EventServiceProvider',EventServiceProvider::class]);
    }

    protected function registerLogBindings()
    {
        $this->singleton('log', function () {
            return $this->loadComponent(
                'app',
                [LogServiceProvider::class],
                'log'
            );
        });
    }

    protected function registerValidatorBindings()
    {
        $this->singleton('validator', function () {
            $this->register('Illuminate\Validation\ValidationServiceProvider');

            return $this->make('validator');
        });
    }

    protected function registerTranslationBindings()
    {
        $this->singleton('translator', function () {
            $this->configure('app');

            $this->instance('path.lang', $this->getLanguagePath());

            $this->register('Illuminate\Translation\TranslationServiceProvider');

            return $this->make('translator');
        });
    }

    /**
     * Load the Eloquent library for the application.
     *
     * @return void
     */
    public function withEloquent()
    {
        $this->make('db');
    }
    /**
     * Register the facades for the application.
     *
     * @param  bool  $aliases
     * @param  array $userAliases
     * @return void
     */
    public function withFacades($aliases = true, $userAliases = [])
    {
        Facade::setFacadeApplication($this);

        if ($aliases) {
            $this->withAliases($userAliases);
        }
    }

    /**
     * Register the aliases for the application.
     *
     * @param  array  $userAliases
     * @return void
     */
    public function withAliases($userAliases = [])
    {
        $defaults = [
            'Illuminate\Support\Facades\DB' => 'DB',
            'Illuminate\Support\Facades\Event' => 'Event',
            'Illuminate\Support\Facades\Log' => 'Log',
            'Illuminate\Support\Facades\Validator' => 'Validator',
        ];

        if (! static::$aliasesRegistered) {
            static::$aliasesRegistered = true;

            $merged = array_merge($defaults, $userAliases);

            foreach ($merged as $original => $alias) {
                class_alias($original, $alias);
            }
        }
    }

}