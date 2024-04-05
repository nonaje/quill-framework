<?php

declare(strict_types=1);

namespace Quill\Links;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Exceptions\Http\RouteNotFound;
use Psr\Http\Message\ResponseInterface;

final readonly class IdentifySearchedRoute implements MiddlewareInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    /**
     * @throws RouteNotFound
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->foundRouteOrKill(
            $this->router->routes(),
            $request
        );

        if ($route === null) {
            throw new RouteNotFound;
        }

        $request = $request->withAttribute('route', $route);

        return $handler->handle($request);
    }

    private function foundRouteOrKill(array $routes, ServerRequestInterface $request): null|RouteInterface
    {
        foreach ($routes as $route) {
            if (!$this->matchRequestedUri($route, $request)) continue;

            return $route;
        }

        return null;
    }

    private function matchRequestedUri(RouteInterface $route, ServerRequestInterface $request): bool
    {
        if ($route->method()->value !== strtoupper($request->getMethod())) {
            return false;
        }

        $routeParts = array_values(array_filter(explode('/', $route->uri())));
        $searchedRouteParts = array_values(array_filter(explode('/', $request->getUri()->getPath())));

        if (count($routeParts) !== count($searchedRouteParts)) {
            return false;
        }

        // Check if route is exactly matched
        if (count(array_diff($routeParts, $searchedRouteParts)) === 0) {
            return true;
        }

        $replacedRegisteredUriWithParameters = [];
        // Check if route can match based on parameters
        foreach ($routeParts as $key => $part) {
            $replacedRegisteredUriWithParameters[$key] = $part;
            if (str_starts_with($part, ':') && isset($searchedRouteParts[$key]) && is_scalar($searchedRouteParts[$key])) {
                $replacedRegisteredUriWithParameters[$key] = $searchedRouteParts[$key];
            }
        }

        if (count(array_diff($replacedRegisteredUriWithParameters, $searchedRouteParts)) === 0) {
            return true;
        }

        return false;
    }
}
