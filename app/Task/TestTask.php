<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-15
 * Time: 下午5:40
 */

namespace App\Task;


use Library\Swoole\Contracts\TaskHandler;

class TestTask extends TaskHandler
{
    function execute($server, $taskId, $workerId, $params)
    {var_dump($params);
        echo "task execute";
        // TODO: Implement handler() method.
    }

    function finishCallBack()
    {
        // TODO: Implement finishCallBack() method.
    }

}