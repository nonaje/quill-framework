<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Core\Enum\HttpMethod;
use App\Core\Patterns\Singleton;
use Closure;
use LogicException;


/**
 * @method Router get(string $uri, Closure|array $target)
 * @method Router post(string $uri, Closure|array $target)
 * @method Router put(string $uri, Closure|array $target)
 * @method Router patch(string $uri, Closure|array $target)
 * @method Router delete(string $uri, Closure|array $target)
 */
final class Router extends Singleton
{
    protected function __construct(
        private readonly RouteStore $store,
        private readonly RouterDispatcher $dispatcher,
        private readonly MiddlewareValidator $middleware
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
        $this->dispatcher->store($this->store)->dispatch();
    }

    public function middleware(Closure|string|array $middleware): self
    {
        $middleware = $this->middleware->validate($middleware);

        $route = $this->store->routes()[$this->store->count() - 1];

        $this->store->update(Route::make(
            uri: $route->uri,
            method: $route->method,
            target: $route->target,
            params: $route->params,
            middlewares: array_merge(
                $route->middlewares,
                is_array($middleware) ? array_values($middleware) : [$middleware]
            )
        ));

        return $this;
    }

    private function addRoute(string $method, string $uri, Closure|array $target): self
    {
        $this->store->add(Route::make(
            uri: $uri,
            method: $method,
            target: $target,
        ));

        return $this;
    }

    public function __call(string $method, array $arguments = [])
    {
        if (! in_array(strtoupper($method), HttpMethod::values())) {
            throw new LogicException("Undefined method " . self::class . "@$method");
        }

        return $this->addRoute($method, ...$arguments);
    }
}
