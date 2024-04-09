<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Enums\Http\HttpMethod;
use Quill\Exceptions\FileNotFoundException;
use Quill\Support\Traits\CanHasMiddlewares;

final readonly class Router implements RouterInterface
{
    use CanHasMiddlewares;

    public function __construct(
        private RouteStoreInterface         $store,
        private MiddlewareStoreInterface    $middlewares,
        private string                      $prefix = ''
    )
    {
    }

    public function __call(string $method, array $arguments = []): RouteInterface
    {
        if (in_array(strtoupper($method), HttpMethod::values())) {
            return $this->map($method, ...$arguments);
        }

        throw new LogicException("Undefined method " . self::class . "@$method");
    }

    /**
     * @throws FileNotFoundException
     */
    public function loadRoutesFrom(string ...$filenames): RouterInterface
    {
        // TODO: Check if it is possible to transfer the logic to a class (Single Responsibility Principle)
        foreach ($filenames as $filename) {
            if (! file_exists($filename)) {
                throw new FileNotFoundException($filename);
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

    public function routes(): array
    {
        return $this->store->all();
    }
}
