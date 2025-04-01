<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use \Closure;
use Psr\Http\Message\UriInterface;
use Quill\Enums\Http\HttpMethod;
use Quill\Router\Route;
use Quill\Router\RouteStore;

interface RouterInterface
{
    public function get(string $path, Closure|array|string $target, array $middlewares = []): void;
    public function post(string $path, Closure|array|string $target, array $middlewares = []): void;
    public function put(string $path, Closure|array|string $target, array $middlewares = []): void;
    public function patch(string $path, Closure|array|string $target, array $middlewares = []): void;
    public function delete(string $path, Closure|array|string $target, array $middlewares = []): void;
    public function head(string $path, Closure|array|string $target, array $middlewares = []): void;
    public function options(string $path, Closure|array|string $target, array $middlewares = []): void;
    public function group(string $prefix, Closure $routes, array $middlewares = []): void;

    /**
     * @return RouteInterface[]
     */
    public function routes(): array;
    public function clear(): void;
}
