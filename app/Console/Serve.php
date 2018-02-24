<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-2-24
 * Time: 上午10:11
 */

namespace App\Console;
use App\Providers\SwooleServiceProvider;

class Serve
{
    protected $startFile;
    protected $commons;

    public function __construct( $startFile )
    {
        $this->startFile = $startFile;
    }

    /**
     * 启动
     * @param null $operations
     */
    public function start( $operations = null )
    {
        echo "swoole development server started: <http://".config( "swoole.listen" ).":".config( "swoole.port" ).">\n";

        if ( trim( $operations, "-") === 'd') {
            config( [ "swoole.settings.daemonize" => true ] );
        }

       $swoole = app()->register(SwooleServiceProvider::class);
       $swoole->start();
    }

    /**
     * 停止
     * @param null $operations
     */
    public function stop( $operations = null )
    {
        $pidFile = config( "swoole.settings.pid_file" );

        if ( trim( $operations, "-") === 'force' || empty($pidFile) || !file_exists($pidFile)){

            exec("ps aux | grep $this->startFile | grep -v grep | awk '{print $2}'", $info);
            if (count($info) <= 1) {
                echo "PHP [$this->startFile] not run\n";
            } else {
                echo "swoole server [$this->startFile] stop success";
                exec("ps aux | grep $this->startFile | grep -v grep | awk '{print $2}' |xargs kill -SIGINT", $info);
            }
        }else{

            $pid = file_get_contents($pidFile);
            if(!\swoole_process::kill($pid,0)){
                echo "pid :{$pid} not exist \n";
                return;
            }
            \swoole_process::kill($pid);
            $time = time();
            while (true){
                usleep(1000);
                if(\swoole_process::kill($pid,0)){
                    echo "swoole server stop at ".date("y-m-d h:i:s")."\n";
                    unlink($pidFile);
                    break;
                }else{
                    if(time() - $time > 2){
                        echo "server stop fail, please try again... \n";
                        break;
                    }
                }
            }
        }
    }

    /**
     * 重启
     * @param null $operations
     */
    public function reload($operations = null )
    {
        if( trim( $operations, "-") === "task"){
            $sign = SIGUSR2;
        }else{
            $sign = SIGUSR1;
        }

        $pidFile = config( "swoole.settings.pid_file" );

        if(!file_exists($pidFile)){
            echo "pid file :{$pidFile} not exist \n";
            return;
        }
        $pid = file_get_contents($pidFile);
        if(!\swoole_process::kill($pid,0)){
            echo "pid :{$pid} not exist \n";
            return;
        }
        \swoole_process::kill($pid, $sign);
        echo "send server reload command at ".date("y-m-d h:i:s")."\n";
    }


    /**
     * 查看状态
     */
    public function status()
    {
        echo  shell_exec("ps aux | grep $this->startFile");
    }


}