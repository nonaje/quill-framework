<?php

declare(strict_types=1);

namespace Quill\Request;

use Quill\Router\Route;

// TODO: PSR-7 Implementation
class Request
{
    private null|Route $route = null;

    public function body(): array
    {
        return ['foo' => 'bar'];
    }

    public function route(Route $route = null): null|Route
    {
        return $this->route ?: $this->route = $route;
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
