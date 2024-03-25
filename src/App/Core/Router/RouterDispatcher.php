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
        private Config $config
    ) { }

    public function dispatch(): void
    {
        $this->matchIncomingRequest();

        if ($this->request->route() === null) {
            $this->response->sendRouteNotFound();
        }

        $this->sendRequestThroughMiddlewares();

        $this->executeRouteTarget();
    }

    public function store(RouteStore $store): self
    {
        $this->store = $store;

        return $this;
    }

    private function matchIncomingRequest(): void
    {
        foreach ($this->store->routes() as $route) {
            if ($route->method !== $this->request->method()) {
                continue;
            }

            $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $route->uri);

            if (preg_match("#^$pattern$#", $this->request->uri(), $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $this->store->update(Route::make(
                    uri: $route->uri,
                    method: $route->method,
                    target: $route->target,
                    params: $params,
                    middlewares: $route->middlewares
                ));

                $this->request->route($route);
                return;
            }
        }

        $this->response->sendRouteNotFound();
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

    private function executeRouteTarget(): void
    {
        // TODO: Inversion of control
        (new RouteTargetExecutor())($this->request, $this->response);
    }
}