<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Enum\Http\HttpMethod;

readonly class Router implements RouterInterface
{
    public function __construct(
        protected FilesLoader              $routeFilesLoader,
        protected RouteStoreInterface      $store,
        protected MiddlewareStoreInterface $middlewares,
        protected string                   $prefix = ''
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

    public function loadRoutesFrom(string ...$filenames): self
    {
        foreach ($filenames  as $filename) {
            if (! file_exists($filename)) {
                throw new InvalidArgumentException("File: $filename does not exists");
            }

            $routes = require $filename;

            if (is_callable($routes)) {
                $routes($this);
            }
        }

        return $this;
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
        $group = $this->store->addGroup(
            RouteGroup::make(
                prefix: $this->prefix . $prefix,
                routes: $routes,
                middlewares: clone $this->middlewares
            )
        );

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
