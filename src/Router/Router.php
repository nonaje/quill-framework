<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Enum\HttpMethod;

class Router implements RouterInterface
{
    public function __construct(
        protected readonly RouteStoreInterface      $store,
        protected readonly MiddlewareStoreInterface $middlewares,
        protected readonly string                   $prefix = ''
    )
    {
    }

    public function __call(string $method, array $arguments = [])
    {
        if (in_array(strtoupper($method), HttpMethod::values())) {
            return $this->map($method, ...$arguments);
        }

        throw new LogicException("Undefined method " . self::class . "@$method");
    }

    public function map(string $method, string $uri, Closure|array $target): RouteInterface
    {
        $route = $this->store->add(Route::make(
            uri: $this->prefix . trim($uri, '/'),
            method: $method,
            target: $target,
            middlewares: clone $this->middlewares
        ));

        $this->middlewares->reset();

        return $route;
    }

    public function group(string $prefix, Closure $routes): RouteGroupInterface
    {
        $prefix = $this->prefix . $prefix;

        $group = $this->store->addGroup($prefix, $routes)
            ->middleware($this->middlewares->all());

        $this->middlewares->reset();

        return $group;
    }

    public function middleware(string|array|Closure|MiddlewareInterface $middleware): self
    {
        $this->middlewares->add($middleware);

        return $this;
    }

    public function routes(): array
    {
        return $this->store->all();
    }
}
