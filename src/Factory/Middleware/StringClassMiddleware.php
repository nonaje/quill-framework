<?php

declare(strict_types=1);

namespace Quill\Factory\Middleware;

use Closure;
use LogicException;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Router\MiddlewareInterface;

final class StringClassMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $middleware)
    {
        $this->assert();
    }

    private function assert(): void
    {
        $registeredMiddlewares = config("app.middlewares", []);
        $middlewareIsNotRegistered = !in_array($this->middleware, $registeredMiddlewares);
        if ($middlewareIsNotRegistered) {
            throw new LogicException("Middleware: '{$this->middleware}' is not registered in app config");
        }

        $isNotInstanceOfMiddlewareInterface = !is_a($this->middleware, MiddlewareInterface::class, true);
        if ($isNotInstanceOfMiddlewareInterface) {
            throw new LogicException("Middleware: '{$this->middleware}' must implement MiddlewareInterface");
        }
    }

    public function handle(RequestInterface $request, Closure $next): void
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = new $this->middleware;

        $middleware->handle($request, $next);
    }
}