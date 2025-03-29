<?php

namespace Quill\Router;

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;

trait Middlewares
{
    private readonly MiddlewareStoreInterface $middlewares;

    /**
     * Add a new middleware to the collection
     *
     * @param string|array|Closure|MiddlewareInterface $middleware
     * @return static
     */
    public function middleware(string|array|Closure|MiddlewareInterface $middleware): static
    {
        $this->middlewares->add($middleware);

        return $this;
    }

    /**
     * @return MiddlewareStoreInterface
     */
    public function getMiddlewares(): MiddlewareStoreInterface
    {
        return $this->middlewares;
    }
}
