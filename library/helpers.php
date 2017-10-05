<?php

use Illuminate\Container\Container;

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $make
     * @return mixed
     */
    function app($make = null)
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath().($path ? '/'.$path : $path);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        return app()->basePath()."/storage".($path ? '/'.$path : $path);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param  string  $content
     * @param  int     $status
     * @param  array   $headers
     * @return \Illuminate\Http\Response
     */
   /* function response($content = '', $status = 200, array $headers = [])
    {
        $factory = new Laravel\Lumen\Http\ResponseFactory;

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($content, $status, $headers);
    }*/
}

if (! function_exists('dispatch')) {
    /**
     * @param $event
     * @param array $params
     */
    function dispatch($event, $params=[]){
        app('events')->dispatch($event, $params);
    }
}

if (! function_exists('task')) {
    /**
     * @param $abstract
     * @param $params
     * @param int $workerId
     */
    function task($abstract, $params = [], $workerId = -1){
        app("task")->task($abstract, $params, $workerId);
    }
}
if (! function_exists('syncTask')) {
    /**
     * @param $abstract
     * @param $params
     * @param float $timeout
     * @param int $workerId
     * @return mixed
     */
    function syncTask($abstract, $params = [],  $timeout = 0.5, $workerId = -1){
       return app("task")->task($abstract, $params, $workerId, $timeout);
    }
}
if (! function_exists('barrier')) {
    /**
     * @return \Library\Swoole\Task\Barrier
     */
    function barrier(){
       return new \Library\Swoole\Task\Barrier();
    }
}


if (! function_exists('serializeClosure')) {
    /**
     * @param $abstract
     * @param $params
     * @return \Library\Swoole\Contracts\SuperClosure
     */
    function serializeClosure($abstract, $params){
        return new \Library\Swoole\Contracts\SuperClosure($abstract, $params);
    }
}

if (! function_exists('db')) {

    /**
     * @return \Illuminate\Database\MySqlConnection|\Illuminate\Database\PostgresConnection
     */
    function db(){
       return app("db");
    }
}


