<?php

declare(strict_types=1);

namespace Quill\Request;

use Psr\Http\Message\ServerRequestInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Router\Route;
use Quill\Support\Pattern\Singleton;

class Request implements RequestInterface
{
    private null|Route $route = null;

    public function __construct(
        private readonly ServerRequestInterface $psrRequest
    )
    {
    }

    public function psrRequest(): ServerRequestInterface
    {
        return $this->psrRequest;
    }

    public function body(): array
    {
        return json_decode($this->psrRequest()->getBody()->getContents(), true);
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->route->params()[$key] ?? $default;
    }

    public function setMatchedRoute(RouteInterface $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getMatchedRoute(): null|RouteInterface
    {
        return $this->route;
    }

    public function method(): string
    {
        return $this->route?->method()->value ?? $_SERVER['REQUEST_METHOD'];
    }

    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }
}
