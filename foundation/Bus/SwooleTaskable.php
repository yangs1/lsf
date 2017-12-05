<?php

namespace Foundation\Bus;

trait SwooleTaskable
{
    public $in_task = false;

    public $is_task_wait = false;

    public $task_id = -1;

    public $timeout = 0.5;

    /**
     * @param bool $in_task
     */
    public function setInTask($in_task)
    {
        $this->in_task = $in_task;
    }

    /**
     * @param bool $is_task_wait
     */
    public function setIsTaskWait($is_task_wait)
    {
        $this->is_task_wait = $is_task_wait;
    }

    /**
     * @param int $task_id
     */
    public function setTaskId($task_id)
    {
        $this->task_id = $task_id;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }
}
