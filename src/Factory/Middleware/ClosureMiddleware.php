<?php

declare(strict_types=1);

namespace Quill\Factory\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Container\ContainerInterface;

final readonly class ClosureMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ContainerInterface $container, private Closure $middleware) { }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return ($this->middleware)($request, $handler, $this->container);
    }
}
