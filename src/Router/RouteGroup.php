<?php

namespace Quill\Router;

use Closure;
use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use \LogicException;

readonly class RouteGroup implements RouteGroupInterface
{
    private function __construct(private RouterInterface $router, private MiddlewareStoreInterface $middlewares)
    {
    }

    public static function make(string $prefix, Closure $routes): RouteGroupInterface
    {
        $prefix = str_starts_with($prefix, '/') ? $prefix : '/' . $prefix;
        $prefix = str_ends_with($prefix, '/') ? $prefix : $prefix . '/';

        $router = new Router(new RouteStore, new MiddlewareStore, $prefix);

        $group = new RouteGroup($router, new MiddlewareStore);

        // Register routes inside a group definition
        $routes($router);

        return $group->assert();
    }

    public function routes(MiddlewareStoreInterface $parentMiddlewares = null): array
    {
        $routes = [];
        $groupMiddlewares = $parentMiddlewares ? array_merge_recursive(
            $this->getMiddlewares()->all(),
            $parentMiddlewares->all()
        ) : $this->getMiddlewares()->all();

        foreach ($this->router->routes() as $unsolved) {
            if ($unsolved instanceof RouteInterface) {
                $routeMiddlewares = array_merge_recursive($groupMiddlewares, $unsolved->getMiddlewares()->all());

                $unsolved->getMiddlewares()->reset()->add($routeMiddlewares);

                $routes[] = $unsolved;
                continue;
            }

            if ($unsolved instanceof RouteGroupInterface) {
                $routes = array_merge($routes, $unsolved->routes($groupMiddlewares));
                continue;
            }

            throw new LogicException(
                'We found an invalid route inside a group, please check your groups routes definitions'
            );
        }

        return $routes;
    }

    public function middleware(array|string|Closure|MiddlewareInterface $middleware): RouteGroupInterface
    {
        $this->middlewares->add($middleware);

        return $this;
    }

    public function getMiddlewares(): MiddlewareStoreInterface
    {
        return $this->middlewares;
    }

    // TODO: Review group validations
    private function assert(): RouteGroupInterface
    {
        return $this;
    }
}
