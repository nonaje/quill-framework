<?php

declare(strict_types=1);

namespace Quill\Router;

use LogicException;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouteStoreInterface;

final class RouteStore implements RouteStoreInterface
{
    /** @var array<string, RouteInterface> */
    private array $routes = [];

    /** @var list<string> */
    private array $order = [];

    /** @inheritDoc */
    public function add(RouteInterface $route): RouteInterface
    {
        $key = $this->keyFor($route);

        if (isset($this->routes[$key])) {
            $existing = $this->routes[$key];

            throw new LogicException(sprintf(
                'Route already registered for [%s] %s',
                $existing->getMethod()->value,
                $existing->getUri()->getPath() ?: '/'
            ));
        }

        $this->routes[$key] = $route;
        $this->order[] = $key;

        return $route;
    }

    /** @inheritDoc */
    public function all(): array
    {
        return array_map(fn (string $key): RouteInterface => $this->routes[$key], $this->order);
    }

    /** @inheritDoc */
    public function clear(): void
    {
        $this->routes = [];
        $this->order = [];
    }

    private function keyFor(RouteInterface $route): string
    {
        $path = $this->normalizePath($route->getUri()->getPath());

        return $route->getMethod()->value . ' ' . $path;
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . ltrim($path, '/');

        return $normalized === '//' ? '/' : rtrim($normalized, '/') ?: '/';
    }
}
