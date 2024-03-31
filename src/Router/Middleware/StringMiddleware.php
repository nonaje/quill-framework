<?php

namespace Quill\Router\Middleware;

use Quill\Request\Request;
use Quill\Response\Response;
use Quill\Contracts\MiddlewareInterface;
use \LogicException;

class StringMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $middleware)
    {
        $this->assert();
    }

    public function handle(Request $request, Response $response): void
    {
        $class = config("app.router.middlewares.{$this->middleware}");

        /** @var MiddlewareInterface $middleware */
        $middleware = new $class;

        $middleware->handle($request, $response);
    }

    private function assert(): void
    {
        $class = config("app.router.middlewares.{$this->middleware}", false);

        $middlewareIsNotRegistered = ! $class;
        if ($middlewareIsNotRegistered) {
            throw new LogicException("Middleware: '$this->middleware' is not registered in app config");
        }

        $isNotInstanceOfMiddlewareInterface = ! is_a($class, MiddlewareInterface::class, true);
        if ($isNotInstanceOfMiddlewareInterface) {
            throw new LogicException("Middleware: '{$this->middleware}' must implement MiddlewareInterface");
        }
    }
}