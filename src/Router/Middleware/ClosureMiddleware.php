<?php

namespace Quill\Router\Middleware;

use Closure;
use Quill\Contracts\MiddlewareInterface;
use Quill\Request\Request;
use Quill\Response\Response;

final class ClosureMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Closure $middleware)
    {
    }

    public function handle(Request $request, Response $response): void
    {
        call_user_func($this->middleware, $request, $response);
    }
}