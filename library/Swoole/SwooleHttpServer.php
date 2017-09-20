<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-8
 * Time: 下午10:56
 */

namespace Library\Swoole;


use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Library\Swoole\Contracts\TaskClosure;
use Library\Swoole\Contracts\TaskHandler;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Library\Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SwooleHttpServer
{
    /**
     * The application instance.
     *
     * @var \App\Application
     */
    protected $app;

    protected $config;

    /**
     * @var \swoole_server
     */
    protected $swooleServer;

    /**
     * Router constructor.
     *
     * @param  $app
     * @param  $config
     */
    public function __construct($app)
    {
        $this->app = $app;


        $this->bootstrapSwoole();
    }

    /**
     * @return \swoole_server  $swooleServer
     */
    function getServer(){
        return $this->swooleServer;
    }

    public function bootstrapSwoole(){
        /*if (!isset($this->config)){
            throw new \Exception('the swoole server necessary config repository.');
        }*/
        if (config('swoole.wss')){
            $this->swooleServer = new \swoole_websocket_server(config('swoole.listen'), config('swoole.port'));
        }else{
            $this->swooleServer = new \swoole_http_server(config('swoole.listen'), config('swoole.port'));
        }
        $this->app->instance('swoole',$this->swooleServer);
    }

    public function start()
    {
        if (!$this->swooleServer instanceof \swoole_server){
            throw new \Exception('swoole server init fail.');
        }
        $this->swooleServer->set(config('swoole.settings'));
        $this->registerEvents('beforeStart', [$this->app], false);
        $this->registerEvents('start');
        $this->registerEvents('shutdown');

        $this->registerEvents('workerStart');
        $this->registerEvents('workerStop');
        $this->registerEvents('workererror');

        $this->registerTaskEvent(); //TODO REQUEST
        $this->registerFinishEvent();//TODO REQUEST

        $this->registerRequestEvent();

        $this->swooleServer->start();
        //TODO REQUEST
    }

    public function registerEvents($event, $params = [], $server=true){
        if ($server){
            $this->swooleServer->on($event, function (...$args) use($event){
                app('events')->dispatch("swoole.{$event}", (array)$args);
            });
        }else{
            app('events')->dispatch("swoole.{$event}", $params);
        }
    }

    public function registerRequestEvent()
    {
        if ( app('events')->hasListeners("swoole.request") ){
            $this->registerEvents("request");
            return ;
        }
        $this->swooleServer->on("request",
            function (\swoole_http_request $request,\swoole_http_response $response){


                $SRequest = new SwooleRequest($request);

                if (0 === strpos($SRequest->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
                    &&
                    in_array(strtoupper($SRequest->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
                ) {
                    parse_str($SRequest->getContent(), $data);
                    $SRequest->request = new ParameterBag($data);
                }
                $baseRequest = Request::createFromBase($SRequest);

                $SResponse = $this->app->handle($baseRequest);
               // xdebug_debug_zval('SResponse');
               /* $response->write($SResponse->getContent());
                var_dump($SResponse->getContent());*/
                if ($SResponse instanceof SymfonyResponse) {
                    $this->formatResponse($response, $SResponse);
                } else {
                    $response->end( (string)$SResponse );
                }


                //xdebug_debug_zval('baseRequest');
                //var_dump($this->app['api.router']->getRoutesDispatched());
                /*if (count($this->app->middleware) > 0) {
                    $this->app->callTerminableMiddleware($SResponse);
                }*/
            });
    }

    private function registerTaskEvent(){
        if (empty(config('swoole.settings.task_worker_num'))){
            return ;
        }
        /*if ( app('events')->hasListeners("swoole.task") ){
            $this->registerEvents("task");
            return ;
        }*/
        $this->swooleServer->on("task",function (\swoole_http_server $server, $taskId, $workerId, $abstract){
//var_dump($abstract);
        /*    var_dump($abstract);
            var_dump($abstract("a","b"));*/

            //$taskReutrn = null;
            /*if (!is_null($taskReutrn[0])){
                return $taskReutrn;
            }*/
            /*if ($abstract instanceof \Closure){
                $taskReutrn = call_user_func($abstract);
            }else*/
            if ($abstract instanceof TaskClosure){
                $params = $abstract->getParams();
                $abstract =  $abstract();

                if (is_string($abstract) && class_exists($abstract)){
                    $abstract = new $abstract();
                    if ($abstract instanceof TaskHandler) {
                        return $abstract->execute($server, $taskId, $workerId, $params);
                    }
                }
                return $abstract;
            }
            return null;
        });
    }

    private function registerFinishEvent(){
        if (empty(config('swoole.settings.task_worker_num'))){
            return ;
        }
        $this->swooleServer->on("finish",
            function (\swoole_http_server $server, $taskId, $abstract){
                //TODO 任务结束处理
                app('events')->dispatch("swoole.finish", [$server, $taskId, $abstract]);

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
    protected function formatResponse(\swoole_http_response $response, SymfonyResponse $realResponse)
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
}