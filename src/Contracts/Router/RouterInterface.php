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
    public function get(string $path, Closure|array|string $target): RouteInterface;
    public function post(string $path, Closure|array|string $target): RouteInterface;
    public function put(string $path, Closure|array|string $target): RouteInterface;
    public function patch(string $path, Closure|array|string $target): RouteInterface;
    public function delete(string $path, Closure|array|string $target): RouteInterface;
    public function head(string $path, Closure|array|string $target): RouteInterface;
    public function options(string $path, Closure|array|string $target): RouteInterface;

    /**
     * Register a new routes group.
     *
     * @param string $prefix
     * @param Closure $routes
     *
     * @return RouteGroupInterface
     */
    public function group(string $prefix, Closure $routes): RouteGroupInterface;

    /**
     * Returns all registered routes.
     *
     * The routes within groups are "compiled" and returned as particular routes
     * with their full path and the middlewares, both individual and those inherited by the group.
     *
     * @return RouteInterface[]
     */
    public function routes(): array;

    /**
     * Delete all registered routes, including groups.
     *
     * @return void
     */
    public function clear(): void;
}
