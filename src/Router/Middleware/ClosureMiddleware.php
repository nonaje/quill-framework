<?php

declare(strict_types=1);

namespace Quill\Router\Middleware;

use Closure;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Request\Request;
use Quill\Response\Response;

final readonly class ClosureMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Closure $middleware)
    {
    }

    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next): void
    {
        call_user_func($this->middleware, $request, $response, $next);
    }
}