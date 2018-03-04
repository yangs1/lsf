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
    /**
     * @var \swoole_process
     */
    protected $process;

    protected $pid;

    protected $createPipe = false;

    protected $isStart = false;



    abstract function handle();

    public function onShutDown(){ echo  "111";}

    public function onReceive($message){}

    public function createPipe(){
        return $this->createPipe;
    }


    public function setProcess( $process )
    {
        $this->process = $process;
    }

    public function getProcess()
    {
        var_dump($this->pid);
        return $this->process;
    }


    public function register(Process $process)
    {
        $this->pid = $process->pid;

        //在shutdown关闭服务器时，会向自定义进程发送SIGTERM信号
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);

            Process::signal(SIGTERM,function ()use($process){
                $this->onShutDown();
                swoole_event_del($process->pipe);
                $this->process->exit(0);
            });
        }

        if ( $this->createPipe ){
            swoole_event_add($process->pipe, function() use ($process){
                $message = $process->read(64 * 1024 );

                $this->onReceive($message);
            });
        }

        $this->handle();
    }

    public function start()
    {
        if ( $this->isStart ){
            throw new \Exception("current process is running");
        }

        if ( app()->bound('swoole_server') && $this->process instanceof \swoole_process){
            app()->make('swoole_server')->addProcess( $this->process );
            $this->isStart = true;
        }else{
            throw new \InvalidArgumentException("this driver can`t run in server.");
        }
    }

    public function write( $message )
    {
        $this->process->write( $message );
    }
}