<?php

declare(strict_types=1);

namespace Quill\Router\Middleware;

use Closure;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Router\MiddlewareInterface;

final readonly class ClosureMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Closure $middleware)
    {
    }

    public function handle(RequestInterface $request, Closure $next): void
    {
        call_user_func($this->middleware, $request, $next);
    }
}