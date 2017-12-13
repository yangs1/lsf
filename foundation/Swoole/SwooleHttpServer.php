<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-8
 * Time: 下午10:56
 */

namespace Foundation\Swoole;

use Foundation\Queue\SwooleQueue;
use Illuminate\Http\Request;
use Foundation\Swoole\Contracts\TaskHandler;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SwooleHttpServer
{
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


        $this->bootstrapSwoole();
    }

    /**
     * @return \swoole_server  $swooleServer
     */
    function getServer(){
        return $this->swooleServer;
    }

    public function bootstrapSwoole(){

        if (config('swoole.wss')){

            $this->swooleServer = new \swoole_websocket_server(config('swoole.listen'), config('swoole.port'));
            $this->registerEvents('message');

        }else{

            $this->swooleServer = new \swoole_http_server(config('swoole.listen'), config('swoole.port'));

        }

        $this->container->instance('swoole_server', $this->swooleServer);
        $this->container->instance('swoole', $this);
    }

    public function start()
    {
        if (!$this->swooleServer instanceof \swoole_server){
            throw new \Exception('swoole server init fail.');
        }

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
            function (\swoole_http_request $request,\swoole_http_response $response){

                $this->container->instance('swoole_request' , $request);
                $this->container->instance('swoole_response', $response);

                $SRequest = $this->initRequest($request);

                if ($SRequest->getContentType() === 'json' &&
                    in_array(strtoupper($SRequest->server->get('REQUEST_METHOD', 'GET')), array('POST','PUT', 'DELETE', 'PATCH'))
                ) {
                    $SRequest->query = new ParameterBag(json_decode($request->rawContent(), true));
                }

               // var_dump($SRequest->request);
                //$baseRequest = Request::createFromBase($SRequest);
                //$baseRequest->headers = $SRequest->headers;

               try{
                   $SResponse = $this->container->handle($SRequest);
               }catch (\Exception $e){
                   $exception = $this->container->resolveExceptionHandler();
                   $SResponse = $exception->render($SRequest, $e);
               }
                if ($SResponse instanceof SymfonyResponse) {
                    $this->formatResponse($response, $SResponse);
                } else {
                    $response->end( (string)$SResponse );
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
//TODO swoole queue
            if ($command instanceof SwooleQueue){
                return $this->container->call([$command, 'handle']);
            }
            return 0;
        });
    }

    private function registerFinishEvent(){
        if (empty(config('swoole.settings.task_worker_num'))){
            return ;
        }
        $this->swooleServer->on("finish",
            function (\swoole_http_server $server, $taskId, $abstract){
                //TODO 任务结束处理
                $this->container['events']->dispatch("swoole.finish", [$server, $taskId, $abstract]);
                if ($abstract instanceof TaskHandler) {
                    $abstract->finishCallBack();
                }
            }
        );
    }


    /**
     * @param \swoole_http_response $response
     * @param  $realResponse
     */
    public function formatResponse(\swoole_http_response $response, SymfonyResponse $realResponse)
    {
        // Build header.
        foreach ($realResponse->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }

        // Build cookies.
        foreach ($realResponse->headers->getCookies() as $cookie) {
            $response->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }

        // Set HTTP status code into the swoole response.
        $response->status($realResponse->getStatusCode());

        if ($realResponse instanceof BinaryFileResponse) {
            $response->sendfile($realResponse->getFile()->getPathname());
        } else {
            $response->end($realResponse->getContent());
        }
    }

    public function initRequest(\swoole_http_request $request)
    {
        $get     = isset($request->get) ? $request->get : [];
        $post    = isset($request->post) ? $request->post : [];
        $cookies = isset($request->cookie) ? $request->cookie : [];
        $server  = isset($request->server) ? $request->server : [];
        $header  = isset($request->header) ? $request->header : [];
        $files   = isset($request->files) ? $request->files : [];

        foreach ($server as $key => $value) {
            $server[strtoupper($key)] = $value;
            unset($server[$key]);
        }
        foreach ($header as $key => $value) {
            $_key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $server[$_key] = $value;
        }
        $SRequest = new Request($get, $post, ["_fd"=>$request->fd], $cookies, $files, $server);
        return $SRequest;
    }
}