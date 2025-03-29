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

    /** @var RouteGroupInterface[] $routes */
    private array $groups = [];

    /** @ineritDoc */
    public function add(RouteInterface $route): RouteInterface
    {
        $this->routes[] = $route;

        return $route;
    }

    /** @ineritDoc */
    public function addGroup(RouteGroupInterface $group): RouteGroupInterface
    {
        $this->groups[] = $group;

        return $group;
    }

    /** @ineritDoc */
    public function all(): array
    {
        return array_merge($this->routes, $this->resolveGroupsRoutes());
    }

    /**
     * @return array
     */
    private function resolveGroupsRoutes(): array
    {
        $routes = [];

        foreach ($this->groups as $group) {
            $routes = array_merge($routes, $group->routes());
        }

        return $routes;
    }

    /** @ineritDoc */
    public function clear(): void
    {
        $this->routes = [];
        $this->groups = [];
    }
}
