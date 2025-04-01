<?php

namespace Quill\Router;

use Closure;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Middleware\MiddlewareFactoryInterface;
use Quill\Factory\Middleware\ClosureMiddleware;
use Quill\Factory\Middleware\StringClassMiddleware;
use Quill\Factory\Middleware\StringMiddleware;

class MiddlewareFactory implements MiddlewareFactoryInterface
{
    public function __construct(protected ContainerInterface $container) { }

    public function make(array|string|Closure|MiddlewareInterface $middleware): MiddlewareInterface
    {
        return match (true) {
            is_callable($middleware) => new ClosureMiddleware($this->container, $middleware),

            class_exists($middleware) => new StringClassMiddleware($this->container, $middleware),

            is_string($middleware) => new StringMiddleware($this->container, $middleware),

            default => throw new InvalidArgumentException('Please provide a valid middleware type for creation'),
        };
    }
}