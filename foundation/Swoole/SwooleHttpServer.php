<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-8
 * Time: 下午10:56
 */

namespace Foundation\Swoole;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SwooleHttpServer
{
    use SwooleHelperTrait;
    /**
     * The application instance.
     *
     * @var \Foundation\Application
     */
    protected $container;

    protected $config;

    /**
     * @var \swoole_server
     */
    protected $swooleServer;

    /**
     * Router constructor.
     * @param  $app
     */
    public function __construct($app)
    {
        $this->container = $app;
    }


    public function bootstrapSwoole(){

        if (config('swoole.wss')){

            $this->swooleServer = new \swoole_websocket_server(config('swoole.listen'), config('swoole.port'));
            $this->registerEvents('message');

        }else{
            $this->swooleServer = new \swoole_http_server(config('swoole.listen'), config('swoole.port'));
        }

        $this->container->instance('swoole_server', $this->swooleServer);
        //$this->container->instance('swoole', $this);
    }

    public function start()
    {
        $this->bootstrapSwoole();

       /* if (!$this->swooleServer instanceof \swoole_server){
            throw new \Exception('swoole server init fail.');
        }*/

        $this->swooleServer->set(config('swoole.settings'));

        $this->registerEvents('beforeStart', [$this->swooleServer], false);

        foreach (['start', 'shutdown', 'workerStart', 'workerStop', 'workererror'] as $event) {
            $this->registerEvents($event);
        }

        $this->registerTaskEvent(); //TODO REQUEST
        $this->registerFinishEvent();//TODO REQUEST

        $this->registerRequestEvent();

        $this->swooleServer->start();
        //TODO REQUEST
    }

    public function registerEvents($event, $params = [], $server=true){
        if ($server){
            $this->swooleServer->on($event, function (...$args) use($event){
                $this->container['events']->dispatch("swoole.{$event}", (array)$args);
            });
        }else{
            $this->container['events']->dispatch("swoole.{$event}", $params);
        }
    }

    public function registerRequestEvent()
    {
        if (  $this->container['events']->hasListeners("swoole.request") ){
            $this->registerEvents("request");
            return ;
        }
        $this->swooleServer->on("request",
            function (\swoole_http_request $swooleRequest,\swoole_http_response $swooleResponse){

                $this->container->instance('swoole_request' , $swooleRequest);
                $this->container->instance('swoole_response', $swooleResponse);

                $request = self::initRequest($swooleRequest);

                if ( $request->isJson() &&
                    in_array( $request->getMethod(), array('POST','PUT', 'DELETE', 'PATCH'))
                ) {
                    $request->query = new ParameterBag( json_decode( $swooleRequest->rawContent(), true ) );
                }

               try{
                   $response = $this->container->handle($request);
               }catch (\Exception $e){
                   $exception = $this->container->resolveExceptionHandler();
                   $response = $exception->render($request, $e);
               }

                if ($response instanceof SymfonyResponse) {
                    $this->formatResponse($swooleResponse, $response);
                } else {
                    $swooleResponse->end( $response );
                }

                $this->container->forgetInstance('swoole_response');
                $this->container->forgetInstance('swoole_request');
            });
    }

    private function registerTaskEvent(){
        if (empty(config('swoole.settings.task_worker_num'))){
            return ;
        }

        $this->swooleServer->on("task",function (\swoole_http_server $server, $taskId, $workerId, $command){

            if ($command instanceof SwooleQueue){
                return $this->container->call([$command, 'handle']);
            }
            return $command;
        });
    }

    private function registerFinishEvent(){
        if (empty(config('swoole.settings.task_worker_num'))){
            return ;
        }
        $this->swooleServer->on("finish",
            function (\swoole_http_server $server, $taskId, $command){
                //TODO 任务结束处理
               // $this->container['events']->dispatch("swoole.finish", [$server, $taskId, $abstract]);
                if ($command instanceof SwooleQueue) {
                    return $this->container->call([$command, 'finish']);
                }
                return $command;
            }
        );
    }

}