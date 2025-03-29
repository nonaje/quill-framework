<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;

readonly class RouteGroup implements RouteGroupInterface
{
    use Middlewares;

    public function __construct(
        private string $prefix,
        private Closure $routes,
        private RouterInterface $router,
        private MiddlewareStoreInterface $middlewares
    ) {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        ($this->routes)($this->router);
    }

    public function routes(): array
    {
        $routes = [];

        foreach ($this->router->routes() as $unsolved) {
            $routes = array_merge_recursive($routes, $this->resolve($unsolved));
        }

        return $routes;
    }

    private function resolve(RouteGroupInterface|RouteInterface $unsolved): array
    {
        return match (true) {
            $unsolved instanceof RouteInterface => [$this->mergeMiddlewares($unsolved)],

            $unsolved instanceof RouteGroupInterface => $unsolved->routes(),
        };
    }

    private function mergeMiddlewares(RouteInterface $route): RouteInterface
    {
        // Merge first the group middlewares, after the route middlewares
        $routeMiddlewares = array_merge(
            $this->getMiddlewares()->all(),
            $route->getMiddlewares()->all()
        );

        // Set the new ordered middlewares
        $route->getMiddlewares()->reset()->add($routeMiddlewares);

        return $route;
    }
}
