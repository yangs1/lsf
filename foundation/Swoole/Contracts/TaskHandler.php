<?php

/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-14
 * Time: 下午5:18
 */
namespace Foundation\Swoole\Contracts;

abstract class TaskHandler
{
    abstract function execute($server, $taskId, $workerId, $params);
    abstract function finishCallBack();
}