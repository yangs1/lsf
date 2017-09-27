<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-16
 * Time: 上午7:53
 */

namespace Library\Swoole\Contracts;

use SuperClosure\Serializer;

class SuperClosure
{
    protected $func;
    protected $params;
    protected $serializer;
    protected $serializedJson;

    function __construct($abstract, $params = [])
    {
        $this->func = $this->getClosure($abstract);
        $this->params = $params;
        $this->serializer = new Serializer();
    }

    public function getParams()
    {
        return $this->params;
    }
    function __sleep()
    {
        // TODO: Implement __sleep() method.
        $this->serializedJson = $this->serializer->serialize($this->func);
        return array("serializedJson",'params');
    }
    function __wakeup()
    {
        // TODO: Implement __wakeup() method.
        $this->serializer = new Serializer();
        $this->func = $this->serializer->unserialize($this->serializedJson);
    }
    function __invoke()
    {
        // TODO: Implement __invoke() method.
        /*
         * prevent call before serialized
         */
        $args = func_get_args();

        $func = $this->serializer->unserialize($this->serializedJson);

        return  call_user_func_array($func,$args);
    }

    private function getClosure($abstract)
    {
        if ($abstract instanceof \Closure){
            return $abstract;
        }else{
            /*if (is_string($abstract) && class_exists($abstract)){
                return function ($parameters = []) use ($abstract) {
                    return (new $abstract())->boot($parameters);
                };
            }*/
            return function () use ($abstract) {
                return $abstract;
            };

        }
    }
}