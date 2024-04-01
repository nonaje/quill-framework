<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Enum\HttpMethod;

final class Route implements RouteInterface
{
    private function __construct(
        private readonly string               $uri,
        private readonly HttpMethod           $method,
        private readonly Closure|array        $target,
        private readonly array                $params,
        private readonly RouteMiddlewareStore $middlewares
    )
    {
    }

    public static function make(
        string               $uri,
        string               $method,
        array|Closure        $target,
        array                $params = [],
        RouteMiddlewareStore $middlewares = null
    ): self
    {
        $uri = str_starts_with($uri, '/') ? $uri : '/' . $uri;

        $route = new self(
            uri: $uri,
            method: HttpMethod::{strtoupper($method)},
            target: $target,
            params: $params,
            middlewares: $middlewares ?? new RouteMiddlewareStore()
        );

        return $route->assert();
    }

    private function assert(): self
    {
        if (!str_starts_with($this->uri, '/')) {
            throw new LogicException(
                "URI $this->uri must starts with '/'"
            );
        }

        if (!$this->method instanceof HttpMethod) {
            throw new LogicException(
                "Please provide a valid HTTP method for URI $this->uri"
            );
        }

        if (!is_callable($this->target) && !is_array($this->target)) {
            throw new LogicException(
                "The route target must be of type array or callable, given " . gettype($this->target)
            );
        }

        if (is_array($this->target)) {
            if (count($this->target) < 1) {
                throw new LogicException(
                    "The route target can't be an empty array"
                );
            }

            $controller = $this->target[0];
            $method = $this->target[1] ?? '__invoke';

            if (!class_exists($controller)) {
                throw new LogicException(
                    "Please provide a valid controller class, provided: $controller"
                );
            }

            if (!method_exists($controller, $method)) {
                throw new LogicException(
                    "Please provide a valid controller method, provided: $controller@$method"
                );
            }
        }

        if (!is_array($this->params)) {
            throw new LogicException(
                "Invalid route params: " . implode(', ', $this->params)
            );
        }

        return $this;
    }

    public function middleware(string|array|Closure|MiddlewareInterface $middleware): self
    {
        $this->middlewares->add($middleware);

        return $this;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function method(): HttpMethod
    {
        return $this->method;
    }

    public function target(): Closure|array
    {
        return $this->target;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function middlewares(): RouteMiddlewareStore
    {
        return $this->middlewares;
    }
}
