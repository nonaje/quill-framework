<?php

declare(strict_types=1);

namespace Quill\Factory\Middleware;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class ClosureMiddleware implements MiddlewareInterface
{
    public function __construct(private Closure $middleware)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return call_user_func($this->middleware, $request, $handler);
    }
}