<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-3-4
 * Time: 上午11:07
 */

namespace App\Process;


use Foundation\Swoole\Process\ProcessInterface;

class DemoProcess extends ProcessInterface
{
    protected $createPipe = true;

    function handle()
    {
        var_dump($this->process->pid);
        /*while (true){
            //var_dump( " run success");
            sleep(1);
        }*/
    }

    public function onReceive( $message )
    {
        var_dump($message);
    }

}