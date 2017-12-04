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

    protected $inTask = false;

    protected $task_id = -1;

    /**
     * Create a new pending job dispatch.
     *
     * @param  mixed  $job
     * @param  $inTask  $job
     * @return void
     */
    public function __construct($job, $inTask = false)
    {
        $this->job = $job;
        $this->inTask = $inTask;
    }

    /**
     * Set the desired connection for the job.
     *
     * @param  string|null  $connection
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
     * @param  string|null  $queue
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
     * @param  \DateTime|int|null  $delay
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
     * @param  array  $chain
     * @return $this
     */
    public function chain($chain)
    {
        $this->job->chain($chain);

        return $this;
    }

    private function pushPipe (){

        $pipe = [];
        if ($this->job instanceof SwooleQueue){
            if ($this->job->delay){
                $pipe[] = function ($request, Closure $next){
                    swoole_timer_after($this->job->delay*1000, function() use($next, $request){
                        $next($request);
                    });
                };
            }

            if ($this->inTask){
                $pipe[] = function ($request, Closure $next){
                    app('swoole_server')->task($request, $this->task_id);
                };
            }
        }

        return $pipe;
    }

    public function task($taskId)
    {
        $this->task_id = $taskId;

        return $this;
    }
    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        app(Dispatcher::class)->pipeThrough($this->pushPipe())->dispatch($this->job);
    }
}
