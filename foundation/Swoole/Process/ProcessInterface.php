<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-3-3
 * Time: 上午10:51
 */

namespace Foundation\Swoole\Process;

use Swoole\Process;

abstract class ProcessInterface
{
    protected $process;

    protected $isAsync = false;

    abstract function handle();

    public function onShutDown(){

    }

    public function onReceive($message)
    {

    }

    public function register(Process $process)
    {
        $this->process = $process;

        Process::signal(SIGTERM,function () use($process){
            $this->onShutDown();
           // TableManager::getInstance()->get('process_hash_map')->del(md5($this->processName));
            swoole_event_del($process->pipe);
            $process->exit(0);
        });

        if ( $this->isAsync ){
            swoole_event_add($process->pipe, function() use ($process){
                $message = $process->read(64 * 1024);
                $this->onReceive($message);
            });
        }
    }

    public function isAsync()
    {
        return $this->isAsync;
    }
}