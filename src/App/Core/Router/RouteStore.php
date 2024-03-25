<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Core\Patterns\Singleton;

class RouteStore extends Singleton
{
    /** @var Route[] $routes */
    private array $routes = [];

    public function add(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function remove(Route $route): bool
    {
        $index = $this->find($route);

        if (is_integer($index)) {
            unset($this->routes[$index]);
        }

        return is_integer($index);
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

    private function find(Route $searched): null|int
    {
        $index = null;

        foreach ($this->routes as $key => $route) {
            if ($route->method === $searched->method && $route->uri === $searched->uri) {
                $index = $key;
            }
        }

        return $index;
    }
}
