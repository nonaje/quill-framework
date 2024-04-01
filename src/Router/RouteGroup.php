<?php

namespace Quill\Router;

use Closure;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Router\RouteStoreInterface;

readonly class RouteGroup implements RouteGroupInterface
{
    private function __construct(private RouterInterface $router)
    {
    }

    public static function make(string $prefix, Closure $routes, RouteStoreInterface $store): RouteGroupInterface
    {
        $prefix = str_starts_with($prefix, '/') ? $prefix : '/' . $prefix;
        $prefix = str_ends_with($prefix, '/') ? $prefix : $prefix . '/';

        $router = new Router($store, $prefix);
        $routes($router);

        $group = new RouteGroup($router);

        return $group->assert();
    }

    // TODO: Review group validations
    private function assert(): RouteGroupInterface
    {
        return $this;
    }

    public function resolveRoutes(): array
    {
        $routes = [];

        foreach ($this->router->routes() as $route) {
            if ($route instanceof RouteInterface) {
                $routes[] = $route;
            }

            if ($route instanceof RouteGroupInterface) {
                $routes = array_merge($routes, $route->resolveRoutes());
            }
        }

        return $routes;
    }
}
