<?php

namespace App\Jobs;

use Foundation\Bus\SwooleTaskable;
use Foundation\Queue\SwooleQueue;
use Illuminate\Bus\Queueable;
use Foundation\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Foundation\Bus\Dispatchable;

//若需要马上执行获取返回值 可把 shouldQueue 删除
class SendReminderEmail implements ShouldQueue //implements SwooleQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SwooleTaskable;

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
       // sleep(25);
        //并发测试 -n 1000 -c 900
        var_dump(microtime(true));
        var_dump("execute over");
        /*cache()->delete("count");
        cache()->add("count",1,60);*/
        $count = cache()->get("count",0);
        cache()->increment('count',1);

        /*$count = app('redis')->get("count",0);
        var_dump($count);
        $count = app('redis')->increment("count", 1);*/
        var_dump($count);
    }

    public function failed($e)
    {
        var_dump('...');
    }

}
