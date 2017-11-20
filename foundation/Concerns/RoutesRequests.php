<?php

namespace Foundation\Concerns;

use Closure;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;
use FastRoute\Dispatcher;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Foundation\Routing\Pipeline;
use Illuminate\Contracts\Support\Responsable;
use Foundation\Routing\Closure as RoutingClosure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Foundation\Routing\Controller as LumenController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait RoutesRequests
{
    /**
     * All of the global middleware for the application.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * All of the route specific middleware short-hands.
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * The current route being dispatched.
     *
     * @var array
     */
    protected $currentRoute;

    /**
     * The FastRoute dispatcher.
     *
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    /**
     * Add new middleware to the application.
     *
     * @param  Closure|array  $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        if (! is_array($middleware)) {
            $middleware = [$middleware];
        }

        $this->middleware = array_unique(array_merge($this->middleware, $middleware));

        return $this;
    }

    /**
     * Define the route middleware for the application.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function routeMiddleware(array $middleware)
    {
        $this->routeMiddleware = array_merge($this->routeMiddleware, $middleware);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request)
    {

        //var_dump($this->router);
        $response = $this->dispatch($request);
        /*if ($this->runningInModel() === "api"){
        }else{
            $response = $this->dispatch($request);
        }*/
        if (count($this->middleware) > 0) {
            $this->callTerminableMiddleware($response);
        }

        return $response;
    }

    public function checkVersion($request, $version){
        if (!($this->parseAccept($request)['version'] === $version)){
            throw new NotFoundHttpException("This version has not been found ,Your version is not accepted ");
        }


    }

    /**
     * Run the application and send the response.
     *
     * @param  SymfonyRequest|null  $request
     * @return void
     */
    public function run($request = null)
    {
        $response = $this->dispatch($request);

        if ($response instanceof SymfonyResponse) {
            $response->send();
        } else {
            echo (string) $response;
        }

        if (count($this->middleware) > 0) {
            $this->callTerminableMiddleware($response);
        }
    }

    /**
     * Call the terminable middleware.
     *
     * @param  mixed  $response
     * @return void
     */
    protected function callTerminableMiddleware($response)
    {
        if ($this->shouldSkipMiddleware()) {
            return;
        }

        $response = $this->prepareResponse($response);

        foreach ($this->middleware as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            $instance = $this->make(explode(':', $middleware)[0]);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($this->make('request'), $response);
            }
        }
    }

    /**
     * Dispatch the incoming request.
     *
     * @param  Request|null  $request
     * @return Response
     */
    public function dispatch($request = null)
    {

        list($method, $pathInfo) = $this->parseIncomingRequest($request);
        try {
            return $this->sendThroughPipeline($this->middleware, function () use ($method, $pathInfo) {
                if (isset($this->router->getRoutes()[$method.$pathInfo])) {
                    if ($this->runningInModel() === "api"){
                        $this->checkVersion($this['request'], $this->router->getRoutes()[$method.$pathInfo]['version']);
                    }

                    return $this->handleFoundRoute([true, $this->router->getRoutes()[$method.$pathInfo]['action'], []]);
                }
                throw new NotFoundHttpException;
                //return $this->handleDispatcherResponse([0]);
                // $this->createDispatcher()->dispatch($method, $pathInfo)
            });
        } catch (Exception $e) {
            return $this->prepareResponse($this->sendExceptionToHandler($e));
        } catch (Throwable $e) {
            return $this->prepareResponse($this->sendExceptionToHandler($e));
        }
    }

    /**
     * Parse the incoming request and return the method and path info.
     *
     * @param  \Symfony\Component\HttpFoundation\Request|null  $request
     * @return array
     */
    protected function parseIncomingRequest($request){
        if (! $request) {
            $request = Request::capture();
        }

        $this->instance(Request::class, $this->prepareRequest($request));

        return [$request->getMethod(), '/'.trim($request->getPathInfo(), '/')];
    }

    /**
     * Create a FastRoute dispatcher instance for the application.
     *
     * @return Dispatcher
     */
    protected function createDispatcher()
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function ($r) {
            foreach ($this->router->getRoutes() as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });
    }

    /**
     * Set the FastRoute dispatcher instance.
     *
     * @param  \FastRoute\Dispatcher  $dispatcher
     * @return void
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle the response from the FastRoute dispatcher.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function handleDispatcherResponse($routeInfo)
    {
       /* switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundHttpException;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($routeInfo[1]);
            case Dispatcher::FOUND:
                return $this->handleFoundRoute($routeInfo);
        }*/
        throw new NotFoundHttpException;
    }

    /**
     * Handle a route found by the dispatcher.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function handleFoundRoute($routeInfo)
    {
        $this->currentRoute = $routeInfo;

        $this['request']->setRouteResolver(function () {
            return $this->currentRoute;
        });

        $action = $routeInfo[1];

        // Pipe through route middleware...
        if (isset($action['middleware'])) {
            $middleware = $this->gatherMiddlewareClassNames($action['middleware']);
            $response =  $this->prepareResponse($this->sendThroughPipeline($middleware, function () {
                return $this->callActionOnArrayBasedRoute($this['request']->route());
            }));

            foreach ((array)$middleware as $item) {
                if (! is_string($item)) {
                    continue;
                }
                $instance = $this->make(explode(':', $item)[0]);
                if (method_exists($instance, 'terminate')) {
                    $instance->terminate($this->make('request'), $response);
                }
            }
            return $response;
        }

        return $this->prepareResponse(
            $this->callActionOnArrayBasedRoute($routeInfo)
        );
    }

    /**
     * Call the Closure on the array based route.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function callActionOnArrayBasedRoute($routeInfo)
    {
        $action = $routeInfo[1];

        if (isset($action['uses'])) {
            return $this->prepareResponse($this->callControllerAction($routeInfo));
        }

        foreach ($action as $value) {
            if ($value instanceof Closure) {
                $closure = $value->bindTo(new RoutingClosure);
                break;
            }
        }

        try {
            return $this->prepareResponse($this->call($closure, $routeInfo[2]));
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Call a controller based route.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function callControllerAction($routeInfo)
    {
        $uses = $routeInfo[1]['uses'];

        if (is_string($uses) && ! Str::contains($uses, '@')) {
            $uses .= '@__invoke';
        }

        list($controller, $method) = explode('@', $uses);

        if (! method_exists($instance = $this->make($controller), $method)) {
            throw new NotFoundHttpException;
        }

        if ($instance instanceof LumenController) {
            return $this->callLumenController($instance, $method, $routeInfo);
        } else {
            return $this->callControllerCallable(
                [$instance, $method], $routeInfo[2]
            );
        }
    }

    /**
     * Send the request through a Lumen controller.
     *
     * @param  mixed  $instance
     * @param  string  $method
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function callLumenController($instance, $method, $routeInfo)
    {
        $middleware = $instance->getMiddlewareForMethod($method);

        if (count($middleware) > 0) {
            return $this->callLumenControllerWithMiddleware(
                $instance, $method, $routeInfo, $middleware
            );
        } else {
            return $this->callControllerCallable(
                [$instance, $method], $routeInfo[2]
            );
        }
    }

    /**
     * Send the request through a set of controller middleware.
     *
     * @param  mixed  $instance
     * @param  string  $method
     * @param  array  $routeInfo
     * @param  array  $middleware
     * @return mixed
     */
    protected function callLumenControllerWithMiddleware($instance, $method, $routeInfo, $middleware)
    {
        $middleware = $this->gatherMiddlewareClassNames($middleware);

        return $this->sendThroughPipeline($middleware, function () use ($instance, $method, $routeInfo) {
            return $this->callControllerCallable([$instance, $method], $routeInfo[2]);
        });
    }

    /**
     * Call a controller callable and return the response.
     *
     * @param  callable  $callable
     * @param  array  $parameters
     * @return \Illuminate\Http\Response
     */
    protected function callControllerCallable(callable $callable, array $parameters = [])
    {
        try {
            return $this->prepareResponse(
                $this->call($callable, $parameters)
            );
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Gather the full class names for the middleware short-cut string.
     *
     * @param  string  $middleware
     * @return array
     */
    protected function gatherMiddlewareClassNames($middleware)
    {
        $middleware = is_string($middleware) ? explode('|', $middleware) : (array) $middleware;

        return array_map(function ($name) {
            list($name, $parameters) = array_pad(explode(':', $name, 2), 2, null);

            return array_get($this->routeMiddleware, $name, $name).($parameters ? ':'.$parameters : '');
        }, $middleware);
    }

    /**
     * Send the request through the pipeline with the given callback.
     *
     * @param  array  $middleware
     * @param  \Closure  $then
     * @return mixed
     */
    protected function sendThroughPipeline(array $middleware, Closure $then)
    {
        if (count($middleware) > 0 && ! $this->shouldSkipMiddleware()) {
            return (new Pipeline($this))
                ->send($this->make('request'))
                ->through($middleware)
                ->then($then);
        }

        return $then();
    }

    /**
     * Prepare the response for sending.
     *
     * @param  mixed  $response
     * @return Response
     */
    public function prepareResponse($response)
    {
        if ($response instanceof Responsable) {
            $response = $response->toResponse(Request::capture());
        }

        if (! $response instanceof SymfonyResponse) {
            $response = new Response($response);
        } elseif ($response instanceof BinaryFileResponse) {
            $response = $response->prepare(Request::capture());
        }

        return $response;
    }

    /**
     * Determines whether middleware should be skipped during request.
     *
     * @return bool
     */
    protected function shouldSkipMiddleware()
    {
        return $this->bound('middleware.disable') && $this->make('middleware.disable') === true;
    }

    /**
     * Prepare the given request instance for use with the application.
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Illuminate\Http\Request
     */
    protected function prepareRequest(SymfonyRequest $request)
    {
        if (! $request instanceof Request) {
            $request = Request::createFromBase($request);
        }
        $request->setRouteResolver(function () {
            return $this->currentRoute;
        });
        /*$request->setUserResolver(function ($guard = null) {
            return $this->make('auth')->guard($guard)->user();
        })->setRouteResolver(function () {
            return $this->currentRoute;
        });*/

        return $request;
    }

    protected function parseAccept(Request $request)
    {
        $config = $this->make('config')->get("app");
        $pattern = '/application\/'.$config['standardsTree'].'\.('.$config['subtype'].')\.([\w\d\.\-]+)\+([\w]+)/';

        if (! preg_match($pattern, $request->header('accept'), $matches)) {
            if ($config['strict']) {
                throw new BadRequestHttpException('Accept header could not be properly parsed because of a strict matching process.');
            }

            $default = 'application/'.$config['standardsTree'].'.'.$config['subtype'].'.'.$config['version'].'+'.$config['defaultFormat'];

            preg_match($pattern, $default, $matches);
        }

        return array_combine(['subtype', 'version', 'format'], array_slice($matches, 1));
    }
}
