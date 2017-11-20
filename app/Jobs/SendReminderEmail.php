<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Foundation\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Foundation\Bus\Dispatchable;
use Swoole\Mysql\Exception;

//若需要马上执行获取返回值 可把 shouldQueue 删除
class SendReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//var_dump('handle');
throw new Exception("test Exception");
        return "11";
        //
    }

    public function failed($payload, $e)
    {
        var_dump($payload);
    }
}
