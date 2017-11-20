<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-10-10
 * Time: 下午2:13
 */

namespace Foundation\Concerns;

use Exception;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

trait ExceptionTransformHandler
{


    /**
     * Handle a generic error response if there is no handler available.
     *
     * @param \Exception $exception
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\Response
     */

    protected function genericResponse(Exception $exception)
    {
        $replacements = $this->prepareReplacements($exception);

        $response = $this->getFormat();

        array_walk_recursive($response, function (&$value, $key) use ($exception, $replacements) {
            if (starts_with($value, ':') && isset($replacements[$value])) {
                $value = $replacements[$value];
            }
        });
        $response = $this->recursivelyRemoveEmptyReplacements($response);

        return new Response($response, $this->getStatusCode($exception), $this->getHeaders($exception));
    }

    /**
     * Prepare the replacements array by gathering the keys and values.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    protected function prepareReplacements(Exception $exception)
    {
        $statusCode = $this->getStatusCode($exception);

        if (! $message = $exception->getMessage()) {
            $message = sprintf('%d %s', $statusCode, Response::$statusTexts[$statusCode]);
        }

        $replacements = [
            ':message' => $message,
            ':status_code' => $statusCode,
        ];

        if ($code = $exception->getCode()) {
            $replacements[':code'] = $code;
        }

        if ($this->runningInDebugMode()) {
              $replacements[':debug'] = [
                  'line' => $exception->getLine(),
                  'file' => $exception->getFile(),
                  'class' => get_class($exception),
                  'trace' => explode("\n", $exception->getTraceAsString()),
              ];
          }

        return $replacements;
    }

    /**
     * Get the status code from the exception.
     *
     * @param \Exception $exception
     *
     * @return int
     */
    protected function getStatusCode(Exception $exception)
    {
        return $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
    }

    /**
     * Recursirvely remove any empty replacement values in the response array.
     *
     * @param array $input
     *
     * @return array
     */
    protected function recursivelyRemoveEmptyReplacements(array $input)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = $this->recursivelyRemoveEmptyReplacements($value);
            }
        }

        return array_filter($input, function ($value) {
            if (is_string($value)) {
                return ! starts_with($value, ':');
            }

            return true;
        });
    }

    /**
     * Get the headers from the exception.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    protected function getHeaders(Exception $exception)
    {
        return $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];
    }

    protected function getFormat(){
        return config("app.formats");
    }

    protected function runningInDebugMode(){
        return config("app.app_debug");
    }
}