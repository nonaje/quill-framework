<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use \Closure;

class RouteStore implements RouteStoreInterface
{
    private null|Route $current = null;

    /** @var array<empty, empty>|RouteInterface[] $routes */
    private array $routes = [];

    /** @var array<empty, empty>|RouteGroupInterface[] $routes */
    private array $groups = [];

    public function add(RouteInterface $route): RouteInterface
    {
        $this->routes[] = $route;

        return $route;
    }

    public function addGroup(string $prefix, Closure $routes): RouteGroupInterface
    {
        $group = RouteGroup::make($prefix, $routes, new self);

        $this->groups[] = $group;

        return $group;
    }

    public function remove(RouteInterface $route): bool
    {
        $index = $this->find($route);

        if (is_integer($index)) {
            unset($this->routes[$index]);
        }

        return is_integer($index);
    }

    private function find(Route $searched): null|int
    {
        foreach ($this->all() as $key => $route) {
            if ($route->method() === $searched->method() && $route->uri() === $searched->uri()) {
                return $key;
            }
        }

        return null;
    }

    public function update(RouteInterface $route): bool
    {
        $index = $this->find($route);

        if (is_integer($index)) {
            $this->routes[$index] = $route;
        }

        return is_integer($index);
    }

    public function current(RouteInterface $route = null): null|RouteInterface
    {
        return $route ? $this->current = $route : $this->current;
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function groups(): array
    {
        return $this->groups;
    }

    public function all(): array
    {
        return array_merge($this->routes(), $this->resolveRouteGroups());
    }

    public function count(): int
    {
        return count($this->routes);
    }

    private function resolveRouteGroups(): array
    {
        $routes = [];

        foreach ($this->groups() as $group) {
            $routes = array_merge($routes, $group->resolveRoutes());
        }


        return $routes;
    }
}
