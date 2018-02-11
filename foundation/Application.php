<?php

/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-9
 * Time: 上午9:38
 */
namespace Foundation;

use Foundation\Component\CommonHandler;
use Foundation\Component\RegisterBindingsHandler;
use Foundation\Component\RegistersExceptionHandler;
use Foundation\Component\RequestsParseHandler;
use Foundation\Routing\Router;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

class Application extends Container{

    use RegistersExceptionHandler, RegisterBindingsHandler, RequestsParseHandler, CommonHandler;
    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The Router instance.
     * @var \Foundation\Routing\Router
     */
    public $router;

    /**
     * The Swoole instance.
     */
    //public $swoole;

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

    /*public $availableBindings = [
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
        'bus'       =>  'registerBusBindings',
        'filesystem' => 'registerFilesystemBindings'
    ];*/
     public $availableBindings = [ 'db','bus','log', 'hash', 'files','cache','redis','queue','events',
         'cookie','config', 'session', 'encrypter', 'validator', 'translator', 'bus', 'filesystem' ];

    protected $aliases = [
        'request'                           => 'Illuminate\Http\Request',
        'Foundation\Http\Request'           => 'Illuminate\Http\Request',
        'Psr\Log\LoggerInterface'           => 'log',
        'Illuminate\Session\SessionManager' =>  'session',
        'Illuminate\Contracts\Queue\Factory'          => 'queue',
        'Illuminate\Contracts\Bus\Dispatcher'         => 'bus',
        'Illuminate\Contracts\Events\Dispatcher'      => 'events',
        'Illuminate\Contracts\Validation\Factory'     => 'validator',
        'Illuminate\Filesystem\FilesystemManager'     => 'filesystem',
        'Illuminate\Contracts\Filesystem\Factory'     => 'filesystem',
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

        $this->availableBindings = array_flip( $this->availableBindings );

        $this->registerBaseBindings();

        $this->registerErrorHandling();

        //var_dump($this->make('config')->get('app.cipher'));
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

        $this->instance('app', $this);
    }


    /**
     * Bootstrap the router instance.
     *
     * @return void
     */
    public function bootstrapRouter()
    {
        $this->router = new Router($this, false);
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
        if ( isset($this->availableBindings[$abstract]) &&
            ! isset($this->ranServiceBinders[$abstract]) ) {

            $this->{'register'.ucfirst($abstract).'Bindings'}();

            $this->ranServiceBinders[$abstract] = true;
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
            return rtrim( $this->basePath , '/').($path ? '/'.$path : $path);
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
     * @param  array|string  $providers
     * @param  string|null  $return
     * @return mixed
     */
    public function loadComponent( $providers, $return = null)
    {
        foreach ((array) $providers as $provider) {
            $this->register($provider);
        }
        if ($return){
            return $this->make($return);
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