<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-26
 * Time: 下午10:27
 */

namespace Library\Swoole\Task;


class Barrier
{
    private $tasks = array();
    private $aliases = array();
    private $results = array();

    public function task($abstract, $params = [], $aliases = null){
        $closure = serializeClosure($abstract, $params);
        $this->tasks[] = $closure;

        if (!is_null($aliases)){
            $this->aliases[$aliases] = count($this->tasks) - 1;
        }
        return $this;
    }
    function execute($timeout = 0.5){
        $this->results = [];
        $this->results = app('swoole')->taskWaitMulti($this->tasks, $timeout);
        $this->flush();
        return $this->results;
    }

    public function flush(){
        $this->aliases =[];
        $this->tasks = [];
    }
    function getResults($aliases = null){
        if (is_null($aliases)){
            return $this->results;
        }
        return $this->results[$this->aliases[$aliases]];
    }
}