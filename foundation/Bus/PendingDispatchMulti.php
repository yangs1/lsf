<?php

namespace Foundation\Bus;

use Closure;
use Foundation\Queue\SwooleQueue;
use Illuminate\Contracts\Bus\Dispatcher;

class PendingDispatchMulti
{
    /**
     * The job.
     *
     * @var mixed
     */
    protected $job;

    public $timeout = 0.5;
    /**
     * Create a new pending job dispatch.
     *
     * @param  mixed $job
     */
    public function __construct($job)
    {
        $this->job = $job;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }



    public function execute()
    {
        return app('swoole_server')->taskWaitMulti($this->job, $this->timeout);
    }

}
