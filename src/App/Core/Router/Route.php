<?php

declare(strict_types=1);

namespace App\Core\Router;

use Closure;

final class Route
{
    public readonly string $uri;

    public function __construct(
        string $uri,
        public readonly string $method,
        public readonly Closure|array $target,
        private array $params = [],
        private array $middlewares = []
    ) {
        $this->uri = $uri === '' ? '/' : $uri;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function middleware(Closure|string|array $middleware): self
    {
        $this->middlewares = array_merge(
            $this->middlewares,
            is_array($middleware) ? array_values($middleware) : [$middleware]
        );

        return $this;
    }

    public function params(array $params): self
    {
        $this->params = $params;

        return $this;
    }
}