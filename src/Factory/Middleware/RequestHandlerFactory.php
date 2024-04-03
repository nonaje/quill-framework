<?php

declare(strict_types=1);

namespace Quill\Factory\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerFactory
{
    public static function createRequestHandler(MiddlewareInterface $middleware, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        return new readonly class ($middleware, $handler) implements RequestHandlerInterface {
            public function __construct(
                private MiddlewareInterface $middleware,
                private RequestHandlerInterface $handler
            )
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->middleware->process($request, $this->handler);
            }
        };
    }
}