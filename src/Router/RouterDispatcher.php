<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Config\Config;
use Quill\Request\Request;
use Quill\Response\Response;

readonly final class RouterDispatcher
{
    private RouteStore $store;

    public function __construct(
        private Request             $request,
        private Response            $response,
        private Config              $config,
        private RouteTargetExecutor $executor
    )
    {
    }

    public function dispatch(): void
    {
        $this->matchRoute();

        if ($this->request->route() === null) {
            $this->response->sendRouteNotFound();
        }

        $this->sendRequestThroughMiddlewares();

        // Call the route target
        $this->executor->dispatch($this->request, $this->response);
    }

    private function matchRoute(): void
    {
        foreach ($this->store->routes() as $route) {
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
                $params[$part] = $searchedRouteParts[$key];
            }
        }

        return $params;
    }

    private function sendRequestThroughMiddlewares(): void
    {
        foreach ($this->request->route()->middlewares() as $middleware) {
            if (is_callable($middleware)) {
                $middleware($this->request, $this->response);
                continue;
            }

            $instantiable = class_exists($middleware)
                ? $middleware
                : $this->config->get("app.router.middlewares.$middleware");

            (new $instantiable)($this->request, $this->response);
        }
    }

    public function store(RouteStore $store): self
    {
        $this->store = $store;

        return $this;
    }
}
