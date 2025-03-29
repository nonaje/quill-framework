<?php

declare(strict_types=1);

namespace Quill\Factory\Middleware;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Container\ContainerInterface;

final readonly class StringClassMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ContainerInterface $container, private string $middleware)
    {
        $this->assert();
    }

    private function assert(): void
    {
        $registeredMiddlewares = config('app.middlewares', []);
        $middlewareIsNotRegistered = !in_array($this->middleware, $registeredMiddlewares);
        if ($middlewareIsNotRegistered) {
            throw new LogicException("Middleware: '{$this->middleware}' is not registered in app config");
        }

        $isNotInstanceOfMiddlewareInterface = !is_a($this->middleware, MiddlewareInterface::class, true);
        if ($isNotInstanceOfMiddlewareInterface) {
            throw new LogicException("Middleware: '{$this->middleware}' must implement MiddlewareInterface");
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = new $this->middleware($this->container);

        return $middleware->process($request, $handler);
    }
}
