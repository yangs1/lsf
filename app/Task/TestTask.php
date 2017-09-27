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
    /**
     * 执行 swoole 的任务事件
     * @param $server
     * @param $taskId
     * @param $workerId
     * @param $params
     * @return mixed
     */
    function execute($server, $taskId, $workerId, $params)
    {
        echo "task execute";

        return $this; //触发回调，否则不被触发
        // TODO: Implement handler() method.
    }

    /**
     * 任务结束 回调事件  需要 execute() 返回 TaskHandler类实例才会触发
     */
    function finishCallBack()
    {
        echo 'task finishCallBack';
        // TODO: Implement finishCallBack() method.
    }

}