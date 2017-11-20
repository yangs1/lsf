<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-18
 * Time: 上午8:33
 */

namespace Foundation\Concerns;


use App\Providers\SwooleServiceProvider;

trait RegistersConsole
{

    /**
     * Parse command.
     * php yourfile.php start | reload | stop | status
     *
     * @return void
     */
    public function parse_command()
    {
        // 检查运行命令的参数
        global $argv;
        $start_file = $argv[0];
        // 命令
        $command = isset($argv[1]) ? trim($argv[1]) : '';
        // 子命令, 目前只支持-d -force
        $operations = isset($argv[2]) ? $argv[2] : '';
        // 根据命令做相应处理
        $this->configure('swoole');
        switch($command)
        {
            // 启动 phpspider
            case 'start':
                if ($operations === '-d') {
                    config( [ "swoole.settings.daemonize" => true ] );
                }
                echo "swoole development server started: <http://".config( "swoole.listen" ).":".config( "swoole.port" ).">\n";
                $this->swoole = $this->register(SwooleServiceProvider::class);
                $this->swoole->start();
                break;
            case 'stop':
                if ($operations === "-force"){
                    config( ["swoole.settings.pid_file"=>'']);
                }
                $this->stopServer($start_file);
                break;
            case "reload":
                if($operations === "-task"){
                    $sign = SIGUSR2;
                }else{
                    $sign = SIGUSR1;
                }
                $this->reloadServer($sign);
                break;

            // 显示 phpspider 运行状态
            case 'status':
                exec("ps aux | grep $start_file");
                break;
            // 未知命令
            default :
                echo "Usage: php startfile.php {start|stop|status|reload}\n";
                break;
        }
    }

    function stopServer($start_file){
        $pidFile = config( "swoole.settings.pid_file" );
        if (empty($pidFile) || !file_exists($pidFile)){
            exec("ps aux | grep $start_file | grep -v grep | awk '{print $2}'", $info);
            if (count($info) <= 1) {
                echo "PHP [$start_file] not run\n";
            } else {
                echo "swoole server [$start_file] stop success";
                exec("ps aux | grep $start_file | grep -v grep | awk '{print $2}' |xargs kill -SIGINT", $info);
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

    function reloadServer($sign = SIGUSR1){
        $pidFile = config( "swoole.settings.pid_file" );
        if(isset($options['pidFile'])){
            if(!empty($options['pidFile'])){
                $pidFile = $options['pidFile'];
            }
        }
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


}