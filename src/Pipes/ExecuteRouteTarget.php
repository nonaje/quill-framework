<?php

declare(strict_types=1);

namespace Quill\Pipes;

use Closure;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouteStoreInterface;

final class ExecuteRouteTarget
{
    public function __invoke(RequestInterface $request, Closure $next): ResponseInterface
    {
        $target = $request->getMatchedRoute()->target();

        if (is_callable($target)) {
            return $target($request);
        }

        $controller = $target[0];
        $method = $target[1] ?? '__invoke';

        return (new $controller($request))->{$method}();
    }
}