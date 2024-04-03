<?php

declare(strict_types=1);

namespace Quill\Factory\Middleware;

use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

final readonly class StringMiddleware implements MiddlewareInterface
{
    public function __construct(private string $middleware)
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

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $class = config("app.middlewares.{$this->middleware}");

        /** @var MiddlewareInterface $middleware */
        $middleware = new $class;

        return $middleware->process($request, $handler);
    }
}