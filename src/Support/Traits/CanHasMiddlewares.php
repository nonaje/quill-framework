<?php

namespace Quill\Support\Traits;

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;

trait CanHasMiddlewares
{
    private readonly MiddlewareStoreInterface $middlewares;

    public function middleware(string|array|Closure|MiddlewareInterface $middleware): self
    {
        $this->middlewares->add($middleware);

        return $this;
    }

    public function getMiddlewares(): MiddlewareStoreInterface
    {
        return $this->middlewares;
    }
}