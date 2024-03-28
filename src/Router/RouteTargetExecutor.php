<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Request\Request;
use Quill\Response\Response;

final class RouteTargetExecutor
{
    public function dispatch(Request $request, Response $response): void
    {
        $target = $request->route()->target();

        if (is_callable($target)) {
            $target($request, $response);
            return;
        }

        $controller = $target[0];
        $method = $target[1] ?? '__invoke';

        (new $controller(
            request: $request,
            response: $response
        ))->{$method}(...$request->route()->params());
    }
}