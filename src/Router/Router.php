<?php

namespace Quill\Router;

use Closure;
use Nyholm\Psr7\Uri;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Middleware\MiddlewareFactoryInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Enums\Http\HttpMethod;

class Router implements RouterInterface
{
    public const string PATH_SEPARATOR = '/';

    /** @var RouteInterface[] $routes */
    protected array $routes = [];

    protected array $groupPrefixStack = [];

    protected array $groupMiddlewareStack = [];

    public function __construct(protected MiddlewareFactoryInterface $middlewareFactory) { }

    public function routes(): array
    {
        return $this->routes;
    }

    public function clear(): void
    {
        $this->routes = [];
    }

    public function group(string $prefix, Closure $routes, array $middlewares = []): void
    {
        $prefix = trim($prefix, self::PATH_SEPARATOR);
        $this->groupPrefixStack[] = $prefix;
        $this->groupMiddlewareStack[] = $middlewares;

        $routes($this);

        array_pop($this->groupPrefixStack);
        array_pop($this->groupMiddlewareStack);
    }

    public function get(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::GET, $path, $target, $middlewares);
    }

    public function post(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::POST, $path, $target, $middlewares);
    }

    public function put(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::PUT, $path, $target, $middlewares);
    }

    public function patch(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::PATCH, $path, $target, $middlewares);
    }

    public function delete(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::DELETE, $path, $target, $middlewares);
    }

    public function head(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::HEAD, $path, $target, $middlewares);
    }

    public function options(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::OPTIONS, $path, $target, $middlewares);
    }

    /**
     * @param array<int, array|string|Closure|MiddlewareInterface> $middlewares
     */
    protected function route(HttpMethod $method, string $uri, Closure|array|string $target, array $middlewares = []): void
    {
        $middlewares = array_map(
            fn (array|string|Closure|MiddlewareInterface $middleware) => $this->middlewareFactory->make($middleware),
            array: $middlewares
        );

        $this->routes[] = new Route(
            uri: new Uri($this->groupPrefix() . $this->normalizePath($uri)),
            method: $method,
            target: $target,
            middlewares: $middlewares
        );
    }

    protected function normalizePath(string $path): string
    {
        return self::PATH_SEPARATOR . trim($path, self::PATH_SEPARATOR);
    }

    protected function groupPrefix(): string
    {
        return $this->groupPrefixStack
            ? self::PATH_SEPARATOR . implode(self::PATH_SEPARATOR, $this->groupPrefixStack)
            : '';
    }
}