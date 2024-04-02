<?php

declare(strict_types=1);

namespace Quill\Request;

use Quill\Router\Route;
use Quill\Support\Pattern\Singleton;

// TODO: PSR-7 Implementation
class Request extends Singleton
{
    private null|Route $route = null;

    public function body(): array
    {
        return ['foo' => 'bar'];
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->route->params()[$key] ?? $default;
    }

    public function setMatchedRoute(Route $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getMatchedRoute(): null|Route
    {
        return $this->route;
    }

    public function method(): string
    {
        return $this->route?->method()->value ?? $_SERVER['REQUEST_METHOD'];
    }

    public function uri(): string
    {
        return $this->route?->uri() ?? $_SERVER['REQUEST_URI'];
    }
}
