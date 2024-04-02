<?php

declare(strict_types=1);

namespace Quill\Factory;

use Closure;
use InvalidArgumentException;
use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Router\Middleware\ArrayMiddleware;
use Quill\Router\Middleware\ClosureMiddleware;
use Quill\Router\Middleware\StringClassMiddleware;
use Quill\Router\Middleware\StringMiddleware;

final class MiddlewareFactory
{
    public static function createMiddleware(string|array|Closure|MiddlewareInterface $middleware): MiddlewareInterface
    {
        return match (true) {
            is_a($middleware, MiddlewareInterface::class) => $middleware,
            is_callable($middleware) => self::createMiddlewareFromClosure($middleware),
            is_array($middleware) => self::createMiddlewareFromArray($middleware),
            class_exists($middleware) => self::createMiddlewareFromStringClass($middleware),
            is_string($middleware) => self::createMiddlewareFromString($middleware),
            default => throw new InvalidArgumentException('Please provide a valid middleware type for creation'),
        };
    }

    public static function createMiddlewareFromClosure(Closure $middleware): MiddlewareInterface
    {
        return new ClosureMiddleware($middleware);
    }

    public static function createMiddlewareFromArray(array $middlewares): MiddlewareInterface
    {
        return new ArrayMiddleware($middlewares);
    }

    public static function createMiddlewareFromStringClass(string $middleware): MiddlewareInterface
    {
        return new StringClassMiddleware($middleware);
    }

    public static function createMiddlewareFromString(string $middleware): MiddlewareInterface
    {
        return new StringMiddleware($middleware);
    }
}