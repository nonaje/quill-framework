<?php

declare(strict_types=1);

namespace Quill\Links;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Factory\Middleware\RequestHandlerFactory;

final class ExecuteRouteMiddlewares implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middlewares = array_flatten($request->getAttribute('route')->getMiddlewares()->all());

        if (! $middlewares) {
            return $handler->handle($request);
        }

        /** @var null|RequestHandlerInterface $next */
        $next = null;
        foreach (array_reverse($middlewares) as $key => $middleware) {
            if ($key === 0) {
                $next = RequestHandlerFactory::createRequestHandler($middleware, $handler);
                continue;
            }

            $next = RequestHandlerFactory::createRequestHandler($middleware, $next);
        }

        return $next->handle($request);
    }
}