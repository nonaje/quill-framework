<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Request\Request;
use Quill\Response\Response;

final class RouteTargetCaller
{
    public function __invoke(Request $request, Response $response): void
    {
        $target = $request->getMatchedRoute()->target();

        if (is_callable($target)) {
            $target($request, $response);
            return;
        }

        $controller = $target[0];
        $method = $target[1] ?? '__invoke';

        (new $controller(
            request: $request,
            response: $response
        ))->{$method}();
    }
}