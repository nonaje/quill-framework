<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Enum\HttpMethod;

readonly final class Route
{
    private MiddlewareValidator $middlewareValidator;

    private function __construct(
        public string               $uri,
        public string               $method,
        public Closure|array        $target,
        public array                $params,
        public Closure|string|array $middlewares
    )
    {
    }

    public static function make(
        string               $uri,
        string               $method,
        Closure|array        $target,
        array                $params = [],
        Closure|string|array $middlewares = []
    ): self
    {
        $uri = str_starts_with($uri, '/') ? $uri : '/' . $uri;

        $route = new self(
            uri: $uri,
            method: HttpMethod::{strtoupper($method)}->value,
            target: $target,
            params: $params,
            middlewares: $middlewares
        );

        $route->middlewareValidator = new MiddlewareValidator();

        return $route->assert();
    }

    private function assert(): self
    {
        if (!str_starts_with($this->uri, '/')) {
            throw new LogicException(
                "URI {$this->uri} must starts with '/'"
            );
        }

        if (!in_array($this->method, HttpMethod::values())) {
            throw new LogicException(
                "Please provide a valid HTTP method for URI {$this->uri}"
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

        if (!is_array($this->middlewares)) {
            throw new LogicException(
                "Please provide a valid middleware"
            );
        }

        $this->middlewareValidator->validate($this->middlewares);

        return $this;
    }
}
