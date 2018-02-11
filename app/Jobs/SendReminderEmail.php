<?php

namespace App\Jobs;

use Foundation\Swoole\SwooleQueue;
use Foundation\Bus\Dispatchable;

//若需要马上执行获取返回值 可把 shouldQueue 删除
class SendReminderEmail implements SwooleQueue //implements ShouldQueue //
{
    use Dispatchable;// Queueable,

    protected $value = '';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $value )
    {
        $this->value = $value;
    }

    /**
     * Execute the job.
     *
     * @return mixed
     */
    public function handle(){

        if ($this->value == 1){
            sleep(2);
        }
        return $this->value;
    }

    public function finish()
    {
        return 666;

    }

}
