<?php

declare(strict_types=1);

namespace Quill\Router\Middleware;

use LogicException;
use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Request\Request;
use Quill\Response\Response;

final class StringMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $middleware)
    {
        $this->assert();
    }

    private function assert(): void
    {
        $class = config("app.middlewares.{$this->middleware}", false);

        $middlewareIsNotRegistered = !$class;
        if ($middlewareIsNotRegistered) {
            throw new LogicException("Middleware: '$this->middleware' is not registered in app config");
        }

        $isNotInstanceOfMiddlewareInterface = !is_a($class, MiddlewareInterface::class, true);
        if ($isNotInstanceOfMiddlewareInterface) {
            throw new LogicException("Middleware: '{$this->middleware}' must implement MiddlewareInterface");
        }
    }

    public function handle(Request $request, Response $response): void
    {
        $class = config("app.middlewares.{$this->middleware}");

        /** @var MiddlewareInterface $middleware */
        $middleware = new $class;

        $middleware->handle($request, $response);
    }
}