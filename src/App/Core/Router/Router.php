<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Core\Config\Config;
use App\Core\Patterns\Singleton;
use App\Core\Request\Request;
use App\Core\Response\Response;
use Closure;


/**
 * @method \App\Core\Router\Route get(string $uri, Closure|array $target)
 * @method \App\Core\Router\Route post(string $uri, Closure|array $target)
 * @method \App\Core\Router\Route put(string $uri, Closure|array $target)
 * @method \App\Core\Router\Route patch(string $uri, Closure|array $target)
 * @method \App\Core\Router\Route delete(string $uri, Closure|array $target)
 */
final class Router extends Singleton
{
    private const string GET = 'GET';
    private const string POST = 'POST';
    private const string PUT = 'PUT';
    private const string PATCH = 'PATCH';
    private const string DELETE = 'DELETE';

    private const array AVAILABLE_METHODS = [
        self::GET,
        self::POST,
        self::PUT,
        self::PATCH,
        self::DELETE,
    ];

    private null|Route $matchedRoute = null;

    /** @var array[Route] $routes */
    private array $routes = [];

    private array $middlewares = [];

    protected function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly Config $config
    ) {
        parent::__construct();
    }

    public function load(string $routePath): self
    {
        require_once $routePath;

        return $this;
    }

    public function dispatch(): void
    {
        $this->matchIncomingRequest();

        if ($this->matchedRoute === null) {
            $this->response->sendRouteNotFound();
        }

        $this->sendRequestThroughMiddlewares();

        $this->executeRequest();
    }

    private function matchIncomingRequest(): void
    {
        /** @var Route $route */
        foreach ($this->routes[$this->request->method()] as $route) {
            $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $route->uri);

            if (preg_match("#^$pattern$#", $this->request->uri(), $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $route->params($params);
                $this->matchedRoute = $route;
                return;
            }
        }

        $this->response->sendRouteNotFound();
    }

    private function sendRequestThroughMiddlewares(): void
    {
        foreach ($this->matchedRoute->getMiddlewares() as $middleware) {
            if (is_callable($middleware)) {
                $middleware($this->request, $this->response);
                continue;
            }

            $instantiable = class_exists($middleware)
                ? $middleware
                : $this->config->get("app.router.middlewares.$middleware");

            if (! $instantiable) {
                throw new \LogicException("Please provide a valid middleware, provided middleware: $middleware.");
            }

            (new $instantiable)($this->request, $this->response);
        }
    }

    private function executeRequest(): void
    {
        if (is_callable($this->matchedRoute->target)) {
            $target = $this->matchedRoute->target;
            $target($this->request, $this->response);
            return;
        }

        $controller = $this->matchedRoute->target[0];
        $method = $this->matchedRoute->target[1] ?? '__invoke';

        if (! class_exists($controller)) {
            throw new \LogicException(
                "Please provide a valid controller class, provided controller class: $controller"
            );
        }

        if (! method_exists($controller, $method)) {
            throw new \LogicException(
                "Please provide a valid controller method, provided method: $method."
            );
        }

        (new $controller(
            request: Request::make(),
            response: Response::make()
        ))->{$method}($this->matchedRoute->getParams());
    }

    private function addRoute(string $method, string $uri, Closure|array $target): Route
    {
        $method = self::{strtoupper($method)};

        $route = new Route(
            uri: $uri,
            method: $method,
            target: $target
        );

        $this->routes[$method][$uri] = $route;

        return $route;
    }

    public function __call(string $method, array $arguments = [])
    {
        if (! in_array(strtoupper($method), self::AVAILABLE_METHODS)) {
            throw new \LogicException(
                'Please provide a valid' . self::class .  " method, provided method: $method."
            );
        }

        return $this->addRoute($method, ...$arguments);
    }
}
