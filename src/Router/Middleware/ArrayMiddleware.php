<?php

declare(strict_types=1);

namespace Quill\Router\Middleware;

use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Factory\MiddlewareFactory;
use Quill\Support\Pattern\Pipeline;

final class ArrayMiddleware implements MiddlewareInterface
{
    /** @var MiddlewareInterface[] */
    public array $middlewares;

    public function __construct(array $middlewares)
    {
        $this->middlewares = array_map(
            fn($middleware) => MiddlewareFactory::createMiddleware($middleware),
            array_flatten($middlewares)
        );
    }

    public function handle(RequestInterface $request, \Closure $next): void
    {
        (new Pipeline())
            ->send($request)
            ->using($this->middlewares)
            ->method('handle')
            ->exec();

        $next($request);
    }
}