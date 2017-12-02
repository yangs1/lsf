<?php

namespace Foundation\Bus;

use Closure;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class PendingDispatch
{
    /**
     * The job.
     *
     * @var mixed
     */
    protected $job;

    protected $pipe = [];

    /**
     * Create a new pending job dispatch.
     *
     * @param  mixed  $job
     * @return void
     */
    public function __construct($job)
    {
        $this->job = $job;
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

    private function delayPipe (){
        if ($this->job->delay){
            $this->pipe[] = function ($request, Closure $next){
                swoole_timer_after($this->job->delay*1000, function() use($next, $request){
                    $next($request);
                });
            };

        }
    }
    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        if (!($this->job instanceof ShouldQueue)){
           $this->delayPipe();
        }
        app(Dispatcher::class)->pipeThrough($this->pipe)->dispatch($this->job);
    }
}
