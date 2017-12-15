<?php

namespace Foundation\Concerns;


use Error;
use ErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait RegistersExceptionHandlers
{
    /**
     * Throw an HttpException with the given data.
     *
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function abort($code, $message = '', array $headers = [])
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message);
        }

        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * Set the error handling for the application.
     *
     * @return void
     */
    protected function registerErrorHandling()
    {
        error_reporting(-1);

        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });

        set_exception_handler(function ($e) {
            $this->handleUncaughtException($e);
        });

        register_shutdown_function(function () {
            $this->handleShutdown();
        });
    }

    /**
     * Handle the application shutdown routine.
     *
     * @return void
     */
    protected function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatalError($error['type'])) {
            $this->handleUncaughtException(new FatalErrorException(
                $error['message'], $error['type'], 0, $error['file'], $error['line']
            ));
        }
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatalError($type)
    {
        $errorCodes = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];

        if (defined('FATAL_ERROR')) {
            $errorCodes[] = FATAL_ERROR;
        }

        return in_array($type, $errorCodes);
    }

    /**
     * Send the exception to the handler and return the response.
     *
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendExceptionToHandler($e)
    {
        $handler = $this->resolveExceptionHandler();

        if ($e instanceof Error) {
            $e = new FatalThrowableError($e);
        }

        $handler->report($e);

        return $handler->render($this->make('request'), $e);
    }

    /**
     * Handle an uncaught exception instance.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function handleUncaughtException($e)
    {
        $handler = $this->resolveExceptionHandler();

        if ($e instanceof Error) {
            $e = new FatalThrowableError($e);
        }
        $handler->report($e);

        if (app()->bound('swoole_response') && app()->bound('swoole')){

            $response = $handler->render($this->make('request'), $e);

            if ($response instanceof SymfonyResponse) {
                app('swoole')->formatResponse(app('swoole_response'), $response);
            } else {
                app('swoole_response')->end( (string)$response );
            }
        }else{
            $response = $handler->renderForConsole($this->make('request'), $e);
            $response->send();
        }
    }

    /**
     * Get the exception handler from the container.
     *
     * @return \App\Exceptions\Handler
     */
    public function resolveExceptionHandler()
    {
        return $this->make('Illuminate\Contracts\Debug\ExceptionHandler');
    }
}
