<?php

declare(strict_types=1);

namespace Quill\Links;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Router\Route;

final class ResolveRouteParameters implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');

        $request = $request->withAttribute('route', Route::make(
            uri: $route->uri(),
            method: $route->method()->value,
            target: $route->target(),
            params: $this->resolveRouteParams($route, $request),
            middlewares: $route->getMiddlewares()
        ));

        return $handler->handle($request);
    }

    private function resolveRouteParams(RouteInterface $route, ServerRequestInterface $request): array
    {
        $params = [];

        $routeParts = array_values(array_filter(explode('/', $route->uri())));
        $searchedRouteParts = array_values(array_filter(explode('/', $request->getUri()->getPath())));

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