<?php

namespace Quill\Router\Middleware;

use Quill\Request\Request;
use Quill\Response\Response;
use Quill\Contracts\MiddlewareInterface;
use \LogicException;

class StringClassMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $middleware)
    {
        $this->assert();
    }

    public function handle(Request $request, Response $response): void
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = new $this->middleware;

        $middleware->handle($request, $response);
    }

    private function assert(): void
    {
        $registeredMiddlewares = config("app.router.middlewares");

        $middlewareIsNotRegistered = ! in_array($this->middleware, $registeredMiddlewares);
        if ($middlewareIsNotRegistered) {
            throw new LogicException("Middleware: '{$this->middleware}' is not registered in app config");
        }

        $isNotInstanceOfMiddlewareInterface = ! is_a($this->middleware, MiddlewareInterface::class, true);
        if ($isNotInstanceOfMiddlewareInterface) {
            throw new LogicException("Middleware: '{$this->middleware}' must implement MiddlewareInterface");
        }
    }
}