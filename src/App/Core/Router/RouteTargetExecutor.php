<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Core\Request\Request;
use App\Core\Response\Response;
use LogicException;

final class RouteTargetExecutor
{
    public function __invoke(Request $request, Response $response): void
    {
        if (is_callable($request->route()->target)) {
            $target = $request->route()->target;
            $target($request, $response);
            return;
        }

        $controller = $request->route()->target[0];
        $method = $request->route()->target[1] ?? '__invoke';

        if (! class_exists($controller)) {
            throw new LogicException(
                "Please provide a valid controller class, provided controller class: $controller"
            );
        }

        if (! method_exists($controller, $method)) {
            throw new LogicException(
                "Please provide a valid controller method, provided method: $method."
            );
        }

        (new $controller(
            request: Request::make(),
            response: Response::make()
        ))->{$method}($request->route()->params);
    }
}