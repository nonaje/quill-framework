<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Enum\HttpMethod;
use Quill\Support\Pattern\Singleton;


/**
 * @method Route get(string $uri, Closure|array $target)
 * @method Route post(string $uri, Closure|array $target)
 * @method Route put(string $uri, Closure|array $target)
 * @method Route patch(string $uri, Closure|array $target)
 * @method Route delete(string $uri, Closure|array $target)
 */
final class Router extends Singleton
{
    protected function __construct(
        private readonly RouteStore       $store,
        private readonly RouterDispatcher $dispatcher,
    )
    {
        parent::__construct();
    }

    public function load(string $routePath): self
    {
        require_once $routePath;

        return $this;
    }

    public function dispatch(): void
    {
        $this->dispatcher->store($this->store)->dispatch();
    }

    public function __call(string $method, array $arguments = [])
    {
        if (!in_array(strtoupper($method), HttpMethod::values())) {
            throw new LogicException("Undefined method " . self::class . "@$method");
        }

        return $this->addRoute($method, ...$arguments);
    }

    private function addRoute(string $method, string $uri, Closure|array $target): Route
    {
        return $this->store->add(Route::make(
            uri: trim($uri, '/'),
            method: $method,
            target: $target,
        ));
    }
}
