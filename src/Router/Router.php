<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterDispatcherInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Enum\HttpMethod;
use Quill\Support\Pattern\Singleton;
use function PHPUnit\Framework\equalTo;

class Router extends Singleton implements RouterInterface
{
    protected function __construct(
        protected readonly RouterDispatcherInterface $dispatcher,
    )
    {
        parent::__construct();
    }

    public function __call(string $method, array $arguments = [])
    {
        if (in_array(strtoupper($method), HttpMethod::values())) {
            return $this->map($method, ...$arguments);
        }

        throw new LogicException("Undefined method " . self::class . "@$method");
    }

    public function map(string $method, string $uri, Closure|array $target): RouteInterface
    {
        return $this->dispatcher->store->add(Route::make(
            uri: trim($uri, '/'),
            method: $method,
            target: $target,
        ));
    }
}
