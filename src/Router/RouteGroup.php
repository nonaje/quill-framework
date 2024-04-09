<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Support\Traits\CanHasMiddlewares;

readonly class RouteGroup implements RouteGroupInterface
{
    use CanHasMiddlewares;

    private function __construct(
        private RouterInterface $router,
        private MiddlewareStoreInterface $middlewares
    )
    {
    }

    public static function make(
        string                   $prefix,
        Closure                  $routes,
        MiddlewareStoreInterface $middlewares = new MiddlewareStore
    ): RouteGroupInterface
    {
        $prefix = trim($prefix, '/');
        $prefix = ($prefix == '/') ? $prefix : "/$prefix/";

        $router = new Router(new RouteStore, new MiddlewareStore, $prefix);

        $group = new RouteGroup($router, $middlewares);

        // Register routes inside a group definition
        $routes($router);

        return $group->assert();
    }

    private function assert(): RouteGroupInterface
    {
        // TODO: Review group validations
        return $this;
    }

    public function routes(MiddlewareStoreInterface $parentMiddlewares = null): array
    {
        $routes = [];
        $groupMiddlewares = array_merge(
            array_flatten($this->getMiddlewares()->all()),
            array_flatten($parentMiddlewares ? $parentMiddlewares->all() : [])
        );

        foreach ($this->router->routes() as $unsolved) {
            if ($unsolved instanceof RouteInterface) {
                $routeMiddlewares = array_merge(
                    $groupMiddlewares,
                    array_flatten($unsolved->getMiddlewares()->all())
                );

                if ($routeMiddlewares) {
                    $unsolved->getMiddlewares()->reset()->add($routeMiddlewares);
                }

                $routes[] = $unsolved;
                continue;
            }

            if ($unsolved instanceof RouteGroupInterface) {
                $routes = array_merge($routes, $unsolved->routes());
                continue;
            }

            throw new LogicException(
                'We found an invalid route inside a group, please check your groups routes definitions'
            );
        }

        return $routes;
    }
}
