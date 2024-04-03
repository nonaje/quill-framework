<?php

declare(strict_types=1);

namespace Quill\Pipes;

use Closure;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Router\Route;

final class ResolveRouteParameters
{
    public function __invoke(RequestInterface $request, Closure $next): ResponseInterface
    {
        $route = $request->getMatchedRoute();

        $request->setMatchedRoute(Route::make(
            uri: $route->uri(),
            method: $route->method()->value,
            target: $route->target(),
            params: $this->resolveRouteParams($route, $request),
            middlewares: $route->getMiddlewares()
        ));

        return $next($request);
    }

    private function resolveRouteParams(RouteInterface $route, RequestInterface $request): array
    {
        $params = [];

        $routeParts = array_values(array_filter(explode('/', $route->uri())));
        $searchedRouteParts = array_values(array_filter(explode('/', $request->uri())));

        // Search for every part of the route that starts with ':'
        // and collects each part of the requested URI at that position
        foreach ($routeParts as $key => $part) {
            if (str_starts_with($part, ':') && isset($searchedRouteParts[$key]) && is_scalar($searchedRouteParts[$key])) {

                // Delete the ":" character
                $params[substr($part, 1)] = $searchedRouteParts[$key];
            }
        }

        return $params;
    }
}