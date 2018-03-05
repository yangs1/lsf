<?php

namespace Foundation\Bus;

use Closure;
use Foundation\Swoole\SwooleQueue;
use Illuminate\Contracts\Bus\Dispatcher;


class PendingDispatch
{
    /**
     * The job.
     *
     * @var mixed
     */
    protected $job;

    protected $pipe = [];

    protected $isFinish = false;

    protected $isReady = false;
    /**
     * Create a new pending job dispatch.
     *
     * @param  mixed $job
     * @return void
     */
    public function __construct($job)
    {
        $this->job = $job;
    }

    /**
     * Set the desired connection for the job.
     *
     * @param  string|null $connection
     * @return $this
     */
//    public function onConnection($connection)
//    {
//        $this->job->onConnection($connection);
//
//        return $this;
//    }

    /**
     * Set the desired queue for the job.
     *
     * @param  string|null $queue
     * @return $this
     */
//    public function onQueue($queue)
//    {
//        $this->job->onQueue($queue);
//
//        return $this;
//    }

    /**
     * Set the desired delay for the job.
     *
     * @param  \DateTime|int|null $delay
     * @return $this
     */
//    public function delay($delay)
//    {
//        $this->job->delay($delay);
//
//        return $this;
//    }

    /**
     * Set the jobs that should run if this job is successful.
     *
     * @param  array $chain
     * @return $this
     */
//    public function chain($chain)
//    {
//        $this->job->chain($chain);
//
//        return $this;
//    }


    /**
     * @param Closure $pipe
     * @return $this
     */
    public function addPipe( Closure $pipe ){
        $this->pipe[] = $pipe;
        return $this;
    }

    /**
     * @param int $worker_id
     * @throws \Exception
     */
    protected function asyn( $worker_id = -1 )
    {
        $this->checkSwooleJob();

        $this->pipe[] = function ($command, Closure $next) use ( $worker_id ) {
            app('swoole_server')->task( $command, $worker_id );
        };
        $this->isReady = true;
    }

    protected function checkSwooleJob(){
        if ($this->job instanceof SwooleQueue){
            return true;
        }
        throw new \Exception(" this job don`t implements the SwooleQueue");
    }

    /**
     * @param float $timeout
     * @param int $worker_id
     * @return mixed
     * @throws \Exception
     */
    public function wait( $timeout = 0.5, $worker_id = -1 )
    {
        $this->checkSwooleJob();
        $this->pipe[] = function ($command, Closure $next) use($timeout, $worker_id){
            return app('swoole_server')->taskwait( $command, $timeout, $worker_id );
        };
        $this->isFinish = true;

        return app(Dispatcher::class )->pipeThrough( $this->pipe )->dispatch( $this->job );
    }

    /**
     * Handle the object's destruction.
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->isReady || $this->asyn();

        return $this->isFinish || app(Dispatcher::class )->pipeThrough( $this->pipe )->dispatch( $this->job );
    }
}
