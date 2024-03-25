<?php

declare(strict_types=1);

namespace Quill\Request;

use Quill\Router\Route;
use Quill\Support\Pattern\Singleton;

class Request extends Singleton
{
    private null|Route $route = null;

    public function body(): array
    {
        dd($_SERVER);
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
