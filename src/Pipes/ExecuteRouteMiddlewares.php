<?php

declare(strict_types=1);

namespace Quill\Pipes;

use Closure;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Support\Pattern\Pipeline;

final class ExecuteRouteMiddlewares
{
    public function __invoke(RequestInterface $request, Closure $next): ResponseInterface
    {
        $middlewares = array_flatten($request->getMatchedRoute()->getMiddlewares()->all());

        if ($middlewares) {
            // TODO: Pipeline result must be ResponseInterface and sent to $next() as $response
            (new Pipeline())
                ->send($request)
                ->using($middlewares)
                ->method('handle')
                ->exec();
        }

        return $next($request);
    }
}