<?php

namespace Foundation\Bus;

use Closure;
use Foundation\Queue\SwooleQueue;
use Illuminate\Contracts\Bus\Dispatcher;

class PendingDispatch
{
    /**
     * The job.
     *
     * @var mixed
     */
    protected $job;

    protected $hasExecute = false;

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
    public function onConnection($connection)
    {
        $this->job->onConnection($connection);

        return $this;
    }

    /**
     * Set the desired queue for the job.
     *
     * @param  string|null $queue
     * @return $this
     */
    public function onQueue($queue)
    {
        $this->job->onQueue($queue);

        return $this;
    }

    /**
     * Set the desired delay for the job.
     *
     * @param  \DateTime|int|null $delay
     * @return $this
     */
    public function delay($delay)
    {
        $this->job->delay($delay);

        return $this;
    }

    /**
     * Set the jobs that should run if this job is successful.
     *
     * @param  array $chain
     * @return $this
     */
    public function chain($chain)
    {
        $this->job->chain($chain);

        return $this;
    }

    private function pushPipe()
    {

        $pipe = [];
        if ($this->job instanceof SwooleQueue) {

            if ($this->job->delay && !$this->job->is_task_wait) {

                // array_unshift($pipe, );
                $pipe[] = function ($request, Closure $next) {
                    swoole_timer_after($this->job->delay * 1000, function () use ($next, $request) {
                        $next($request);
                    });
                };
            }

            if ($this->job->in_task) {

                if ($this->job->is_task_wait) {
                    $pipe[] = function ($request, Closure $next) {
                        return app('swoole_server')->taskwait($request, $this->job->timeout, $this->job->task_id);
                    };
                } else {
                    $pipe[] = function ($request, Closure $next) {
                        app('swoole_server')->task($request, $this->job->task_id);
                    };
                }

            }


        }

        return $pipe;
    }

    public function task($taskId = -1)
    {
        $this->job->setInTask(true);

        $this->job->setTaskId($taskId);

        return $this;
    }

    public function taskWait($timeout = 0.5, $taskId = -1)
    {
        $this->job->setIsTaskWait(true);

        $this->job->setTaskId($taskId);

        $this->job->setTimeout($timeout);
        return $this;
    }

    public function execute()
    {
        $this->hasExecute = true;
        return app(Dispatcher::class)->pipeThrough($this->pushPipe())->dispatch($this->job);
    }

    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->hasExecute || $this->execute();
        //app(Dispatcher::class)->pipeThrough($this->pushPipe())->dispatch($this->job);
    }
}
