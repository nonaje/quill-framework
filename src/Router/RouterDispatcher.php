<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterDispatcherInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Request\Request;
use Quill\Response\Response;

readonly final class RouterDispatcher implements RouterDispatcherInterface
{
    public function __construct(
        private Request            $request,
        private Response           $response,
        public RouteStoreInterface $store,
        private RouteTargetCaller  $caller
    )
    {
    }

    public function dispatch(): void
    {
        $route = $this->foundRouteOrKill(
            $this->resolveRoutes()
        );

        if ($route === null) {
            $this->response->sendRouteNotFound();
        }

        $this->walkThroughMiddlewares($route);

        // Call the route target
        $this->caller->__invoke($this->request, $this->response);
    }

    /**
     * @param RouteInterface[] $routes
     */
    private function foundRouteOrKill(array $routes): null|RouteInterface
    {
        foreach ($routes as $route) {
            if (!$this->matchRequestedUri($route)) continue;

            // TODO: Move route params resolution to another step
            // $params = $this->resolveRouteParams($route);

            // $this->store->update($matched = Route::make(
            //     uri: $route->uri(),
            //     method: $route->method()->value,
            //     target: $route->target(),
            //     params: $params,
            //     middlewares: $route->getMiddlewares()
            // ));

            $this->store->setMatchedRoute($route);
            $this->request->setMatchedRoute($route);

            return $route;
        }

        return null;
    }

    private function matchRequestedUri(RouteInterface $route): bool
    {
        if ($route->method()->value !== $this->request->method()) {
            return false;
        }

        $routeParts = array_values(array_filter(explode('/', $route->uri())));
        $searchedRouteParts = array_values(array_filter(explode('/', $this->request->uri())));

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

    /**
     * @return RouteInterface[]
     */
    private function resolveRoutes(): array
    {
        $routes = $this->store->routes();

        foreach ($this->store->groups() as $group) {
            $groupRoutes = $group->routes();

            $routes = array_merge($routes, $groupRoutes);
        }

        return $routes;
    }

    private function walkThroughMiddlewares(RouteInterface $route): void
    {
        foreach ($route->getMiddlewares()->all() as $middleware) {
            $middleware->handle($this->request, $this->response);
        }
    }

    private function resolveRouteParams(RouteInterface $route): array
    {
        $params = [];

        $routeParts = array_values(array_filter(explode('/', $route->uri())));
        $searchedRouteParts = array_values(array_filter(explode('/', $this->request->uri())));

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
