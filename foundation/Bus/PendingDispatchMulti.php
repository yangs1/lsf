<?php

namespace Foundation\Bus;

use Foundation\Swoole\SwooleQueue;

class PendingDispatchMulti
{
    /**
     * The job.
     *
     * @var mixed
     */
    protected $jobs = [];

    protected $timeout = 0.5;

    /**
     * 必须 return 数据
     * Create a new pending job dispatch.
     * @param SwooleQueue $job
     * @return self
     */
    public function addJob ( SwooleQueue $job ){
        $this->jobs[] = $job;
        return $this;
    }

    public function setTimeout( $timeout )
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function execute()
    {
        return app('swoole_server')->taskWaitMulti( $this->jobs, $this->timeout );
    }

}
