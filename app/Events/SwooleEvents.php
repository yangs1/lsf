<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-8
 * Time: 下午10:02
 */

namespace App\Events;


use Foundation\Application;
use Foundation\Queue\SwooleWorker;
use Foundation\Queue\WorkerOptions;

class SwooleEvents
{
    public function beforeStart(Application $app)
    {
      /*  $app['queue'];
        $work = new WorkerOptions();

        $app['queue.worker']->daemon('redis', "lsf",$work);*/

        // TODO: Implement beforeStart() method.
    }

    public function onStart(\swoole_http_server $server)
    {
        // TODO: Implement onStart() method.
    }

    public function onShutdown(\swoole_http_server $server)
    {
        // TODO: Implement onShutdown() method.
    }

    public function onWorkerStart(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStart() method.
    }

    public function onWorkerStop(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStop() method.
    }
    public  function onWorkerError(\swoole_http_server $server, $worker_id, $worker_pid, $exit_code)
    {
        // TODO: Implement onWorkerError() method.
    }

    public function onTask(\swoole_http_server $server, $taskId, $workerId, $taskObj)
    {
        // TODO: Implement onTask() method.
    }

    public function onFinish(\swoole_http_server $server, $taskId, $taskObj)
    {
        // TODO: Implement onFinish() method.
    }

    public function onRequest(\swoole_http_request $request,\swoole_http_response $response)
    {
        // TODO: Implement onRequest() method.
    }

    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        // TODO: Implement onMessage() method.
    }
}