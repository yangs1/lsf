<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/5
 * Time: 下午5:20
 */

namespace Library\Swoole\Task;


class TaskManager
{
    public function task($abstract, $params = [], $workerId = -1, $timeout = 0)
    {
        $task = serializeClosure($abstract, $params);
        if ($timeout === 0){
            return app("swoole_server")->task($task, $workerId);
        }
        return app("swoole_server")->taskwait($task, $timeout, $workerId);
    }

    public function asyTask($abstract, $params = [], $workerId = -1)
    {
        $this->task($abstract, $params, $workerId, 0);
    }

    public function syncTask($abstract, $params, $workerId, $timeout)
    {
        return $this->task($abstract, $params, $workerId, $timeout);
    }

    public function barrier(){
        return new Barrier();
    }
}