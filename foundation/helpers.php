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

if (! function_exists('config_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath()."/config".($path ? '/'.$path : $path);
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

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * @return mixed|\Illuminate\Cache\CacheManager
     *
     * @throws \Exception
     */
    function cache()
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            return app('cache');
        }

        if (is_string($arguments[0])) {
            return app('cache')->get($arguments[0], $arguments[1] ?? null);
        }

        if (! is_array($arguments[0])) {
            throw new Exception(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }

        if (! isset($arguments[1])) {
            throw new Exception(
                'You must specify an expiration time when setting a value in the cache.'
            );
        }

        return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1]);
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

if (! function_exists('event')) {
    /**
     * @param $event
     * @param array $params
     */
    function event($event, $params=[]){
        app('events')->dispatch($event, $params);
    }
}


if (! function_exists('dispatch')) {
    /**
     * Dispatch a job to its appropriate handler.
     *
     * @param  mixed  $job
     * @return \Foundation\Bus\PendingDispatch
     */
    function dispatch($job)
    {
        return new \Foundation\Bus\PendingDispatch($job);
    }
}

if (! function_exists('dispatchMulti')) {
    /**
     * Dispatch a job to its appropriate handler.
     *
     * @param  mixed  $job
     * @return mixed
     */
    function dispatchMulti($job)
    {
        return (new \Foundation\Bus\PendingDispatchMulti($job))->execute();
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
if (! function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     *
     * @param  string  $value
     * @return string
     */
    function encrypt($value)
    {
        return app('encrypter')->encrypt($value);
    }
}

if (! function_exists('decrypt')) {
    /**
     * Decrypt the given value.
     *
     * @param  string  $value
     * @return string
     */
    function decrypt($value)
    {
        return app('encrypter')->decrypt($value);
    }
}

if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string  $id
     * @param  array   $replace
     * @param  string  $locale
     * @return \Illuminate\Contracts\Translation\Translator|string
     */
    function trans($id = null, $replace = [], $locale = null)
    {
        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $replace, $locale);
    }
}

if (! function_exists('trans_choice')) {
    /**
     * Translates the given message based on a count.
     *
     * @param  string  $id
     * @param  int|array|\Countable  $number
     * @param  array   $replace
     * @param  string  $locale
     * @return string
     */
    function trans_choice($id, $number, array $replace = [], $locale = null)
    {
        return app('translator')->transChoice($id, $number, $replace, $locale);
    }
}

//if (! function_exists('formatResponse')) {
//    /**
//     * @param \swoole_http_response $response
//     * @param  $realResponse
//     */
//    function formatResponse(\swoole_http_response $response, SymfonyResponse $realResponse)
//    {
//        // Build header.
//        foreach ($realResponse->headers->allPreserveCase() as $name => $values) {
//            foreach ($values as $value) {
//                $response->header($name, $value);
//            }
//        }
//
//        // Build cookies.
//        foreach ($realResponse->headers->getCookies() as $cookie) {
//            $response->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(),
//                $cookie->getPath(),
//                $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
//        }
//
//        // Set HTTP status code into the swoole response.
//        $response->status($realResponse->getStatusCode());
//
//        if ($realResponse instanceof BinaryFileResponse) {
//            $response->sendfile($realResponse->getFile()->getPathname());
//        } else {
//            $response->end($realResponse->getContent());
//        }
//    }
//}


if (! function_exists('transformData')) {
    /**
     * @param \Illuminate\Database\Eloquent\Model|\Illuminate\support\Collection $data
     * @param \Foundation\Transformer\AbstractTransformer|null $transformer
     * @return \Foundation\Transformer\TransformerEngine
     */
    function transformData($data, $transformer = null)
    {
        return new \Foundation\Transformer\TransformerEngine($data, $transformer);
    }
}



if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Session\Store|\Illuminate\Session\SessionManager
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }

        if (is_array($key)) {
            return app('session')->put($key);
        }

        return app('session')->get($key, $default);
    }
}

if (! function_exists('bcrypt')) {
    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    function bcrypt($value, $options = [])
    {
        return app('hash')->make($value, $options);
    }
}