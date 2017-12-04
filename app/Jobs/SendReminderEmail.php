<?php

namespace App\Jobs;

use Foundation\Queue\SwooleQueue;
use Illuminate\Bus\Queueable;
use Foundation\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Foundation\Bus\Dispatchable;

//若需要马上执行获取返回值 可把 shouldQueue 删除
class SendReminderEmail implements SwooleQueue//implements ShouldQueue
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
//throw new Exception("test Exception");
 //       return "11";
        //
        var_dump("execute over");
    }

    public function failed($e)
    {
        var_dump('...');
    }

}
