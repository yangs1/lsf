<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-3-1
 * Time: 上午10:25
 */

namespace Foundation\Swoole\Process;

use Closure;

class ProcessManager
{
    /**
     * The application instance.
     *
     * @var \Foundation\Application
     */
    protected $app;


    /**
     * The array of resolved Process stores.
     *
     * @var array
     */
    protected $stores = [];


    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new Process manager instance.
     *
     * @param  \Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a Process store instance by name.
     *
     * @param null $name
     * @return ProcessInterface
     */
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->stores[$name] ?? $this->stores[$name] = $this->get($name);
    }

    /**
     * Get a Process driver instance.
     *
     * @param  string  $driver
     * @return ProcessInterface
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }

    /**
     * Attempt to get the store from the local cache.
     *
     * @param  string  $name
     * @return ProcessInterface
     */
    protected function get($name)
    {
        if ( isset( $this->stores[$name] )){
            return $this->stores[$name];
        }
        return $this->resolve($name);
    }

    /**
     * Resolve the given store.
     *
     * @param  string  $name
     * @return ProcessInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        /*$config = $this->getConfig($name);

        if (is_null($config)) {
            throw new \InvalidArgumentException("Cache store [{$name}] is not defined.");
        }*/

        if (isset($this->customCreators[$name])) {

            if ( $this->customCreators[$name] instanceof Closure ){

                $processClass = new ClosureProcess();
                $processClass->setClosure(  $this->customCreators[$name] );

            }else if ( class_exists( $this->customCreators[$name]  )){

                $processClass = new $this->customCreators[$name];
            }

            if (isset($processClass) && $processClass instanceof ProcessInterface){

                 $processClass->setProcess( new \swoole_process( [$processClass,'register'], false, $processClass->createPipe() ? 2 : 0 ) );
                 return $processClass;
            }

            throw new \InvalidArgumentException("Current Callback Driver{$this->customCreators[$name]} is not supported.");

        } else {

            $driverMethod = 'create'.ucfirst($name).'Driver';

            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}();
            } else {
                throw new \InvalidArgumentException("Driver [{$name}] is not supported.");
            }
        }
    }


    /**
     * Call a custom driver creator.
     *
     * @param  string  $name
     * @return mixed
     */
    protected function callCustomCreator($name)
    {
        return $this->customCreators[$name];
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure|string  $callback
     * @return $this
     */
    public function extend($driver, $callback)
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }


    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'default';
    }


}

//{accountId:"3a310828-268d-e711-80e4-da42ba972ebd",extRel1:"3a897d02-268d-e711-80e4-da42ba972ebd",vals:{code:"OpenMall",VerifyStatus:"UnChecked"}}