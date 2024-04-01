<?php

declare(strict_types=1);

namespace Quill\Router;

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
        $this->foundRouteOrKill();
        $this->walkThroughMiddlewares();

        // Call the route target
        $this->caller->__invoke($this->request, $this->response);
    }

    private function foundRouteOrKill(): void
    {
        dd($this->store->all());
        foreach ($this->store->all() as $route) {
            $this->store->current($route);

            $match = $this->matchRequestedUri();

            if (!$match) continue;

            $params = $this->resolveRouteParams();

            $this->store->update($matched = Route::make(
                uri: $route->uri(),
                method: $route->method()->value,
                target: $route->target(),
                params: $params,
                middlewares: $route->middlewares()
            ));

            $this->request->route($matched);
            return;
        }

        $this->response->sendRouteNotFound();
    }

    private function matchRequestedUri(): bool
    {
        $route = $this->store->current();

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

        if (count(array_diff($searchedRouteParts, $replacedRegisteredUriWithParameters)) === 0) {
            return true;
        }

        return false;
    }

    private function resolveRouteParams(): array
    {
        $params = [];
        $route = $this->store->current();

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

    private function walkThroughMiddlewares(): void
    {
        foreach ($this->store->current()->middlewares()->all() as $middleware) {
            $middleware->handle($this->request, $this->response);
        }
    }
}
