<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Config\Config;

final class MiddlewareValidator
{
    public function validate(Closure|string|array $middleware): Closure|string|array
    {
        if (is_callable($middleware)) {
            return $middleware;
        }

        if (is_array($middleware)) {
            foreach ($middleware as $m) $this->validate($m);
            return $middleware;
        }

        // If the provided middleware is a string class, we need to validate that the class exists.
        // If the provided middleware is a simple string, it must match the registered middlewares
        if (!class_exists($middleware) && !Config::make()->get("app.router.middlewares.$middleware", false)) {
            throw new LogicException("Please provide a valid middleware, provided middleware: $middleware");
        }

        return $middleware;
    }
}