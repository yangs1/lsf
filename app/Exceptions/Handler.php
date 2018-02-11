<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Foundation\Component\ExceptionTransformHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;

class Handler implements ExceptionHandler
{
    use ExceptionTransformHandler;


    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     * @param Exception $e
     * @throws Exception
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        try {
            $logger = app('log');
        } catch (Exception $ex) {
            throw $e; // throw the original exception
        }

        $logger->error($e);
    }

    /**
     * Render an exception into an HTTP response.
     * @param \Illuminate\Http\Request $request
     * @param Exception $e
     * @return Exception|Response|null|\Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @throws Exception
     */
    public function render($request, Exception $e)
    {
        $e = $this->filterException( $request, $e);

        if ($e instanceof \Symfony\Component\HttpFoundation\Response){
            return $e;
        };

        if ($request->expectsJson()){

            return $this->genericResponse($e);
        }

        $fe = FlattenException::create($e);

        $handler = new SymfonyExceptionHandler( $this->runningInDebugMode() );

        $decorated = $this->decorate($handler->getContent($fe), $handler->getStylesheet($fe));

        $response = new Response($decorated, $fe->getStatusCode(), $fe->getHeaders());

        $response->exception = $e;

        return $response;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function renderForConsole($request, Exception $e)
    {
        $e = $this->filterException( $request, $e);

        if ($e instanceof \Symfony\Component\HttpFoundation\Response){
            return $e;
        };

        $fe = FlattenException::create($e);
        $response = new Response($e, $fe->getStatusCode(), $fe->getHeaders());
        $response->exception = $e;
        return $response;

    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Exception $e
     * @return Exception|Response|null|\Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     */
    protected function filterException( $request, Exception $e){

        if ($e instanceof HttpResponseException) {

            return $e->getResponse();

        } elseif ($e instanceof ModelNotFoundException) {

            $e = new NotFoundHttpException($e->getMessage(), $e);

        }elseif ($e instanceof ValidationException ) {
            if ($e->getResponse()){
                return $e->getResponse();
            }
            if ($request->expectsJson()){
                return new Response( $e->errors(), 422);
            }
        }

        return $e;
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  \Exception  $e
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return !$this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param  \Exception  $e
     * @return bool
     */
    protected function shouldntReport(Exception $e)
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the html response content.
     *
     * @param  string  $content
     * @param  string  $css
     * @return string
     */
    protected function decorate($content, $css)
    {
        return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta name="robots" content="noindex,nofollow" />
        <style>
            /* Copyright (c) 2010, Yahoo! Inc. All rights reserved. Code licensed under the BSD License: http://developer.yahoo.com/yui/license.html */
            html{color:#000;background:#FFF;}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0;}table{border-collapse:collapse;border-spacing:0;}fieldset,img{border:0;}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal;}li{list-style:none;}caption,th{text-align:left;}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;}q:before,q:after{content:'';}abbr,acronym{border:0;font-variant:normal;}sup{vertical-align:text-top;}sub{vertical-align:text-bottom;}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;}input,textarea,select{*font-size:100%;}legend{color:#000;}
            html { background: #eee; padding: 10px }
            img { border: 0; }
            #sf-resetcontent { width:970px; margin:0 auto; }
            $css
        </style>
    </head>
    <body>
        $content
    </body>
</html>
EOF;
    }
}
