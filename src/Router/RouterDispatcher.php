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
        $this->matchRequestedUri();

        if ($this->request->route() === null) {
            $this->response->sendRouteNotFound();
        }

        $this->sendRequestThroughMiddlewares();

        // Call the route target
        $this->executor->dispatch($this->request, $this->response);
    }

    private function matchRequestedUri(): void
    {
        foreach ($this->store->routes() as $route) {
            if ($route->method()->value !== $this->request->method()) continue;

            [$match, $params] = $this->matchUriPatternAndExtractParameters($route);

            if (!$match) continue;

            $this->store->update($route = Route::make(
                uri: $route->uri(),
                method: $route->method()->value,
                target: $route->target(),
                params: $params,
                middlewares: $route->middlewares()
            ));

            $this->request->route($route);
            return;
        }
    }

    private function matchUriPatternAndExtractParameters(Route $route): array
    {
        $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $route->uri());

        if (preg_match("#^$pattern$#", $this->request->uri(), $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return [true, $params];
        }

        return [false, []];
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
