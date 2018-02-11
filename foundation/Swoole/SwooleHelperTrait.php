<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-2-7
 * Time: 下午11:19
 */

namespace Foundation\Swoole;

use Foundation\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait SwooleHelperTrait
{
    /**
     * @param \swoole_http_response $response
     * @param  $realResponse
     */
    public static function formatResponse(\swoole_http_response $response, SymfonyResponse $realResponse)
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



    public function initRequest(\swoole_http_request $swooleRequest)
    {
        $get     = isset($swooleRequest->get) ? $swooleRequest->get : [];
        $post    = isset($swooleRequest->post) ? $swooleRequest->post : [];
        $cookies = isset($swooleRequest->cookie) ? $swooleRequest->cookie : [];
        $server  = isset($swooleRequest->server) ? $swooleRequest->server : [];
        $header  = isset($swooleRequest->header) ? $swooleRequest->header : [];
        $files   = isset($swooleRequest->files) ? $swooleRequest->files : [];

        $server = array_change_key_case($server, CASE_UPPER);

        foreach ($header as $key => $value) {
            $_key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $server[$_key] = $value;
        }
        $request = new Request($get, $post, ["_fd"=>$swooleRequest->fd], $cookies, $files, $server);
        return $request;
    }
}