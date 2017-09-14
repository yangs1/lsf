<?php

namespace Library\Swoole\Http;

use Dingo\Api\Http\Request as DingoRequest;
use Dingo\Api\Contract\Http\Request as RequestInterface;

class Request extends DingoRequest implements RequestInterface
{
    protected $swooleRequest;

    protected $fd;

    public function __construct(\swoole_http_request $request)
    {

        $this->fd = $request->fd;
        $this->swooleRequest = $request;

        $get     = isset($request->get) ? $request->get : [];
        $post    = isset($request->post) ? $request->post : [];
        $cookies = isset($request->cookie) ? $request->cookie : [];
        $server  = isset($request->server) ? $request->server : [];
        $header   = isset($request->header) ? $request->header : [];
        $files   = isset($request->files) ? $request->files : [];
        foreach ($server as $key => $value) {
            $server[strtoupper($key)] = $value;
            unset($server[$key]);
        }
        foreach ($header as $key => $value) {
            $_key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $server[$_key] = $value;
        }

        parent::__construct($get, $post, [], $cookies, $files, $server);
    }

    public function getFd(){
        return $this->fd;
    }

    public function getOrigin(){
        return $this->swooleRequest;
    }
}
