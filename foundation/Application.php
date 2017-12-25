<?php

/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-9
 * Time: 上午9:38
 */
namespace Foundation;

use Foundation\Concerns\RegisterTrait;
use Foundation\Queue\SwooleWorker;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Foundation\Concerns\RegistersConsole;
use Foundation\Concerns\RoutesRequests;
use Illuminate\Support\ServiceProvider;
use Foundation\Concerns\RegistersExceptionHandlers;
use Foundation\Routing\Router;

class Application extends Container{

    use RegistersExceptionHandlers, RoutesRequests, RegistersConsole, RegisterTrait;
    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The Router instance.
     *
     * @var \Foundation\Routing\Router
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
     * Indicates if the class aliases have been registered.
     *
     * @var bool
     */
    protected static $aliasesRegistered = false;

    public $availableBindings = [
        'db'        =>  'registerDatabaseBindings',
        'log'       =>  'registerLogBindings',
        'hash'      =>  'registerHashingBindings',
        'files'     =>  'registerFilesBindings',
        'cache'     =>  'registerCacheBindings',
        "redis"     =>  'registerRedisBindings',
        'queue'     =>  'registerQueueBindings',
        'events'    =>  'registerEventBindings',
        'cookie'     => 'registerCookieBindings',
        'config'    =>  'registerConfigBindings',
        'session'   =>  'registerSessionBindings',
        'encrypter' =>  'registerEncrypterBindings',
        'validator' =>  'registerValidatorBindings',
        'translator'=>  'registerTranslationBindings',
        'Illuminate\Contracts\Bus\Dispatcher'     => 'registerBusBindings',
        'Illuminate\Filesystem\FilesystemManager' => "registerFilesSystemBindings",
    ];

    protected $aliases = [
        'request'                           => 'Illuminate\Http\Request',
        'filesystem'                        => 'Illuminate\Filesystem\FilesystemManager',
        'Psr\Log\LoggerInterface'           => 'log',
        'Illuminate\Session\SessionManager' =>  'session',
        'Illuminate\Contracts\Queue\Factory'          => 'queue',
        'Illuminate\Contracts\Events\Dispatcher'      => 'events',
        'Illuminate\Contracts\Validation\Factory'     => 'validator',
        'Illuminate\Contracts\Filesystem\Factory'     => 'Illuminate\Filesystem\FilesystemManager',
        'Illuminate\Contracts\Cookie\QueueingFactory' => 'cookie',
        'Illuminate\Contracts\Debug\ExceptionHandler' => 'App\Exceptions\Handler',
        'Illuminate\Contracts\Translation\Translator' => 'translator'
    ];
    /**
     * Create a new Lumen application instance.
     * @param null $basePath
     */
    public function __construct($basePath = null){
       // date_default_timezone_set('Asia/Shanghai');
        $this->basePath = $basePath;

        $this->registerBaseBindings();

        $this->registerErrorHandling();

        $this->bootstrapRouter();
    }


    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->configure('app');

        $this->instance('app', $this);
    }


    /**
     * Bootstrap the router instance.
     *
     * @return void
     */
    public function bootstrapRouter()
    {
        if ($this->runningInModel() === "api"){
            $this->router = new Router($this, true);
        }else{
            $this->router = new Router($this);
        }
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Set the current application locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);

        //   $this['translator']->setLocale($locale);

        //   $this['events']->fire('locale.changed', [$locale]);
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
        return config('app.app_model', 'http');
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



    /**
     * Create the file cache directory if necessary.
     *
     * @param  string  $path
     * @return void
     */
    protected function ensureCacheDirectoryExists($path)
    {
        $files = $this->make('files');
        if (! $files->exists($path)) {
            $files->makeDirectory($path, 0777, true, true);
        }
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
            'Illuminate\Support\Facades\Queue' => 'Queue',
            'Illuminate\Support\Facades\Log' => 'Log',
            'Illuminate\Support\Facades\Validator' => 'Validator',
            'Illuminate\Support\Facades\Storage' => 'filesystem',
            'Illuminate\Support\Facades\Hash' => 'hash',
            'Illuminate\Support\Facades\Cookie' => 'cookie'
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