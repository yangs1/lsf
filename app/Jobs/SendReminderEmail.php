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
//throw new Exception("test Exception");
 //       return "11";
        //
        var_dump("execute over");
    }

    public function failed($payload, $e)
    {
        var_dump($payload);
    }

    public function queue($queue, $command)
    {
      /* if ($command->delay){
           swoole_timer_after($command->delay*1000, function() use($queue, $command){

           });
       }*/
        app('swoole_server')->task($command);
    }
}
