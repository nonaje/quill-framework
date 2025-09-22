<?php

declare(strict_types=1);

namespace Quill\Factory\Middleware;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Container\ContainerInterface;

final readonly class StringMiddleware implements MiddlewareInterface
{
    private MiddlewareInterface $middleware;

    public function __construct(private readonly ContainerInterface $container, string $middleware)
    {
        $this->assert($middleware);
    }

    private function assert(string $middleware): void
    {
        $target = config("app.middlewares.$middleware", false);

        if (false === $target) {
            throw new LogicException("Middleware: '$this->middleware' is not registered in app config");
        }

        if (is_callable($target)) {
            $this->middleware = new ClosureMiddleware($this->container, $target);
            return;
        }

        if (class_exists($target) && is_a($target, MiddlewareInterface::class, true)) {
            $this->middleware = new $target($this->container);
            return;
        }

        throw new LogicException("Middleware: '{$this->middleware}' must implement MiddlewareInterface or be a closure");
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->middleware->process($request, $handler);
    }
}
