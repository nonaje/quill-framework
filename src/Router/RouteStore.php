<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Support\Pattern\Singleton;

class RouteStore extends Singleton
{
    /** @var Route[] $routes */
    private array $routes = [];

    public function add(Route $route): Route
    {
        $this->routes[] = $route;

        return $route;
    }

    public function remove(Route $route): bool
    {
        $index = $this->find($route);

        if (is_integer($index)) {
            unset($this->routes[$index]);
        }

        return is_integer($index);
    }

    private function find(Route $searched): null|int
    {
        foreach ($this->routes as $key => $route) {
            if ($route->method() === $searched->method() && $route->uri() === $searched->uri()) {
                return $key;
            }
        }

        return null;
    }

    public function update(Route $route): bool
    {
        $index = $this->find($route);

        if (is_integer($index)) {
            $this->routes[$index] = $route;
        }

        return is_integer($index);
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function count(): int
    {
        return count($this->routes);
    }
}
