<?php

namespace Quill\Router;

use Closure;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Enums\Http\HttpMethod;

class Router implements RouterInterface
{
    public const string PATH_SEPARATOR = '/';

    protected array $groupPrefixStack = [];
    protected array $groupMiddlewareStack = [];

    public function __construct(
        protected ContainerInterface $container,
        protected RouteStoreInterface $routes,
        protected string $prefix = ''
    ) {
    }

    /** @inheritDoc */
    public function routes(): array
    {
        return $this->routes->all();
    }

    public function clear(): void
    {
        $this->routes->clear();
    }

    /** @inheritDoc */
    public function group(string $prefix, Closure $routes, array $middlewares = []): void
    {
        $prefix = trim($prefix, self::PATH_SEPARATOR);
        $this->groupPrefixStack[] = $prefix;
        $this->groupMiddlewareStack[] = $middlewares;

        $routes($this);

        array_pop($this->groupPrefixStack);
        array_pop($this->groupMiddlewareStack);
    }

    protected function groupPrefix(): string
    {
        return $this->groupPrefixStack
            ? self::PATH_SEPARATOR . implode(self::PATH_SEPARATOR, $this->groupPrefixStack)
            : '';
    }

    /** @ineritDoc */
    protected function route(HttpMethod $method, string $uri, Closure|array|string $target, array $middlewares = []): void
    {
        $this->routes->add(new Route(
            uri: new Uri($this->groupPrefix() . $this->normalizePath($uri)),
            method: $method,
            target: $target,
            middlewares: $this->container->get(MiddlewareStoreInterface::class),
        ));
    }

    protected function normalizePath(string $path): string
    {
        return self::PATH_SEPARATOR . trim($path, self::PATH_SEPARATOR);
    }

    /** @ineritDoc */
    public function get(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::GET, $path, $target, $middlewares);
    }

    /** @ineritDoc */
    public function post(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::POST, $path, $target, $middlewares);
    }

    /** @ineritDoc */
    public function put(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::PUT, $path, $target, $middlewares);
    }

    /** @ineritDoc */
    public function patch(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::PATCH, $path, $target, $middlewares);
    }

    /** @ineritDoc */
    public function delete(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::DELETE, $path, $target, $middlewares);
    }

    /** @ineritDoc */
    public function head(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::HEAD, $path, $target, $middlewares);
    }

    /** @ineritDoc */
    public function options(string $path, array|string|Closure $target, array $middlewares = []): void
    {
        $this->route(HttpMethod::OPTIONS, $path, $target, $middlewares);
    }
}