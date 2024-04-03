<?php

declare(strict_types=1);

namespace Quill\Pipes;

use Closure;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Exceptions\Http\RouteNotFound;

final readonly class IdentifySearchedRoute
{
    public function __construct(private RouteStoreInterface $store)
    {
    }

    /**
     * @throws RouteNotFound
     */
    public function __invoke(RequestInterface $request, Closure $next): ResponseInterface
    {
        $route = $this->foundRouteOrKill(
            $this->resolveRoutes(),
            $request
        );

        if ($route === null) {
            throw new RouteNotFound;
        }

        return $next($request,);
    }

    private function foundRouteOrKill(array $routes, RequestInterface $request): null|RouteInterface
    {
        foreach ($routes as $route) {
            if (!$this->matchRequestedUri($route, $request)) continue;

            $request->setMatchedRoute($route);

            return $route;
        }

        return null;
    }

    private function matchRequestedUri(RouteInterface $route, RequestInterface $request): bool
    {
        if ($route->method()->value !== $request->method()) {
            return false;
        }

        $routeParts = array_values(array_filter(explode('/', $route->uri())));
        $searchedRouteParts = array_values(array_filter(explode('/', $request->uri())));

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

    private function resolveRoutes(): array
    {
        $routes = $this->store->routes();

        foreach ($this->store->groups() as $group) {
            $groupRoutes = $group->routes();

            $routes = array_merge($routes, $groupRoutes);
        }

        return $routes;
    }
}