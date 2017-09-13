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

    protected $swooleServer;

    /**
     * Router constructor.
     *
     * @param  $app
     * @param  $config
     */
    public function __construct($app, $config)
    {
        $this->app = $app;
        $this->config = $config;

        $this->bootstrapSwoole();
    }

    /**
     * @return \swoole_server  $swooleServer
     */
    function getServer(){
        return $this->swooleServer;
    }

    public function bootstrapSwoole(){
        if (!isset($this->config)){
            throw new \Exception('the swoole server necessary config repository.');
        }
        if ($this->config['wss']){
            $this->swooleServer = new \swoole_websocket_server($this->config['listen'], $this->config['port']);
        }else{
            $this->swooleServer = new \swoole_http_server($this->config['listen'], $this->config['port']);
        }
    }

    public function start()
    {
        if (!$this->swooleServer instanceof \swoole_server){
            throw new \Exception('swoole server init fail.');
        }
        $this->swooleServer->set($this->config['settings']);
 //       $this->registerEvents('beforeStart', [$this->swooleServer], false);
//        $this->registerEvents('start');
//        $this->registerEvents('shutdown');
//        $this->registerEvents('workererror');
        $this->registerEvents('task'); //TODO REQUEST
        $this->registerEvents('finish');//TODO REQUEST
//        $this->registerEvents('workerStart');
//        $this->registerEvents('workerStop');
        $this->registerRequestEvent();
        //var_dump($this->app->getA());
        $this->swooleServer->start();
        //TODO REQUEST
    }

    public function registerEvents($event, $params = [], $server=true){
        if ($server){
            $this->getServer()->on($event, function (...$args) use($event){
                //$this->registerEvents("swoole.{$event}", (array)$args);
                $this->app->make('events')->dispatch("swoole.{$event}", (array)$args);
            });
        }else{
            $this->app->make('events')->dispatch("swoole.{$event}", $params);
        }

    }

    public function registerRequestEvent()
    {
        $this->getServer()->on("request",
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

                if ($SResponse instanceof SymfonyResponse) {
                    $this->formatResponse($response, $SResponse);
                } else {
                    $response->end( (string)$SResponse );
                }
                //var_dump($this->app['api.router']->getRoutesDispatched());
                /*if (count($this->app->middleware) > 0) {
                    $this->app->callTerminableMiddleware($SResponse);
                }*/
            });
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