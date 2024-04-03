<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouteStoreInterface;

class RouteStore implements RouteStoreInterface
{
    private null|Route $matchedRoute = null;

    /** @var array<empty, empty>|RouteInterface[] $routes */
    private array $routes = [];

    /** @var array<empty, empty>|RouteGroupInterface[] $routes */
    private array $groups = [];

    public function add(RouteInterface $route): RouteInterface
    {
        $this->routes[] = $route;

        return $route;
    }

    public function addGroup(RouteGroupInterface $group): RouteGroupInterface
    {
        $this->groups[] = $group;

        return $group;
    }

    public function update(RouteInterface $route): bool
    {
        $index = $this->find($route);

        if (is_integer($index)) {
            $this->routes[$index] = $route;
        }

        return is_integer($index);
    }

    private function find(RouteInterface $searched): null|int
    {
        foreach ($this->all() as $key => $route) {
            if ($route->method() === $searched->method() && $route->uri() === $searched->uri()) {
                return $key;
            }
        }

        return null;
    }

    public function all(): array
    {
        return array_merge($this->routes(), $this->resolveGroupsRoutes());
    }

    public function routes(): array
    {
        return $this->routes;
    }

    private function resolveGroupsRoutes(): array
    {
        $routes = [];

        foreach ($this->groups() as $group) {
            $routes = array_merge($routes, $group->routes());
        }

        return $routes;
    }

    public function groups(): array
    {
        return $this->groups;
    }

    public function getMatchedRoute(): RouteInterface
    {
        return $this->matchedRoute;
    }

    public function setMatchedRoute(Route $route): RouteStoreInterface
    {
        $this->matchedRoute = $route;

        return $this;
    }

    public function count(): int
    {
        return count($this->routes);
    }
}
