<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/5
 * Time: 下午5:20
 */

namespace Library\Swoole\Contracts;


class TaskBarrier
{
    private $tasks = array();
    private $aliases = array();
    private $results = array();
    private $totals = 0;

    function add($abstract, $params = [], $aliases = null){
        $closure = serializeClosure($abstract, $params);
        $this->tasks[] = $closure;

        if (!is_null($aliases)){
            $this->aliases[$aliases] = $this->totals;
        }
        $this->totals++;
        return $this;
    }

    function execute($timeout = 0.5){
        $this->results = [];
        $this->results = app('swoole')->taskWaitMulti($this->tasks, $timeout);
        $this->flush();
        return $this->results;
    }

    public function flush(){
        $this->totals = 0;
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