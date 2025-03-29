<?php

namespace Quill\Router;

use Closure;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Quill\Container\Container;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Enums\Http\HttpMethod;

class Router implements RouterInterface
{
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
    public function group(string $prefix, Closure $routes): RouteGroupInterface
    {
        $prefix = $this->prefix . '/' . trim($prefix, '/');

        return $this->routes->addGroup(new RouteGroup(
            prefix: $prefix,
            routes: $routes,
            router: new Router($this->container, new $this->routes, $prefix),
            middlewares: $this->container->get(MiddlewareStoreInterface::class),
        ));
    }

    /** @ineritDoc */
    protected function route(HttpMethod $method, UriInterface $uri, Closure|array|string $target): RouteInterface
    {
        return $this->routes->add(new Route(
            uri: new Uri($this->prefix . '/' . trim($uri->__toString(), '/')),
            method: $method,
            target: $target,
            middlewares: $this->container->get(MiddlewareStoreInterface::class),
        ));
    }

    /** @ineritDoc */
    public function get(string $path, array|string|Closure $target): RouteInterface
    {
        return $this->route(HttpMethod::GET, new Uri($path), $target);
    }

    /** @ineritDoc */
    public function post(string $path, array|string|Closure $target): RouteInterface
    {
        return $this->route(HttpMethod::POST, new Uri($path), $target);
    }

    /** @ineritDoc */
    public function put(string $path, array|string|Closure $target): RouteInterface
    {
        return $this->route(HttpMethod::PUT, new Uri($path), $target);
    }

    /** @ineritDoc */
    public function patch(string $path, array|string|Closure $target): RouteInterface
    {
        return $this->route(HttpMethod::PATCH, new Uri($path), $target);
    }

    /** @ineritDoc */
    public function delete(string $path, array|string|Closure $target): RouteInterface
    {
        return $this->route(HttpMethod::DELETE, new Uri($path), $target);
    }

    /** @ineritDoc */
    public function head(string $path, array|string|Closure $target): RouteInterface
    {
        return $this->route(HttpMethod::HEAD, new Uri($path), $target);
    }

    /** @ineritDoc */
    public function options(string $path, array|string|Closure $target): RouteInterface
    {
        return $this->route(HttpMethod::OPTIONS, new Uri($path), $target);
    }
}