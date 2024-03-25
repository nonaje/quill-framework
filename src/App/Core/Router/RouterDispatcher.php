<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Core\Config\Config;
use App\Core\Request\Request;
use App\Core\Response\Response;

readonly final class RouterDispatcher
{
    private RouteStore $store;
    public function __construct(
        private Request $request,
        private Response $response,
        private Config $config,
        private RouteTargetExecutor $executor
    ) { }

    public function dispatch(): void
    {
        $this->matchRequestedUri();

        if ($this->request->route() === null) {
            $this->response->sendRouteNotFound();
        }

        $this->sendRequestThroughMiddlewares();

        // Call the route target
        $this->executor->dispatch($this->request, $this->response);
    }

    public function store(RouteStore $store): self
    {
        $this->store = $store;

        return $this;
    }

    private function matchRequestedUri(): void
    {
        foreach ($this->store->routes() as $route) {
            if ($route->method !== $this->request->method()) continue;

            [$match, $params] = $this->matchUriPatternAndExtractParameters($route);

            if (! $match) continue;

            $this->store->update($route = Route::make(
                uri: $route->uri,
                method: $route->method,
                target: $route->target,
                params: $params,
                middlewares: $route->middlewares
            ));

            $this->request->route($route);
            return;
        }

        $this->response->sendRouteNotFound();
    }

    private function matchUriPatternAndExtractParameters(Route $route): array
    {
        $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $route->uri);

        if (preg_match("#^$pattern$#", $this->request->uri(), $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return [true, $params];
        }

        return [false , []];
    }

    private function sendRequestThroughMiddlewares(): void
    {
        foreach ($this->request->route()->middlewares as $middleware) {
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
}
