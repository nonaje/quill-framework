<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Contracts\RouterInterface;
use Quill\Enum\HttpMethod;
use Quill\Support\Pattern\Singleton;

class Router extends Singleton implements RouterInterface
{
    protected function __construct(
        private readonly RouteStore       $store,
        private readonly RouterDispatcher $dispatcher,
    )
    {
        parent::__construct();
    }

    public function loadRoutes(Closure $toLoad): self
    {
        $toLoad($this);

        return $this;
    }

    public function dispatch(): void
    {
        $this->dispatcher->store($this->store)->dispatch();
    }

    public function __call(string $method, array $arguments = [])
    {
        if (in_array(strtoupper($method), HttpMethod::values())) {
            return $this->map($method, ...$arguments);
        }

        throw new LogicException("Undefined method " . self::class . "@$method");
    }

    public function map(string $method, string $uri, Closure|array $target): Route
    {
        return $this->store->add(Route::make(
            uri: trim($uri, '/'),
            method: $method,
            target: $target,
        ));
    }
}
