<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouteStoreInterface;

class RouteStore implements RouteStoreInterface
{
    /** @var RouteInterface[] $routes */
    private array $routes = [];

    /** @ineritDoc */
    public function add(RouteInterface $route): RouteInterface
    {
        $this->routes[] = $route;

        return $route;
    }

    /** @ineritDoc */
    public function all(): array
    {
        return $this->routes;
    }

    /** @ineritDoc */
    public function clear(): void
    {
        $this->routes = [];
    }
}
