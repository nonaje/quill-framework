<?php

namespace Quill\Router\Middleware;

use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Factory\MiddlewareFactory;
use Quill\Request\Request;
use Quill\Response\Response;

final class ArrayMiddleware implements MiddlewareInterface
{
    /** @var MiddlewareInterface[] */
    private array $middlewares;

    public function __construct(array $middlewares)
    {
        $this->middlewares = array_map(
            fn($middleware) => MiddlewareFactory::createMiddleware($middleware),
            $middlewares
        );
    }

    public function handle(Request $request, Response $response): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->handle($request, $response);
        }
    }
}