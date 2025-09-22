<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;
use Psr\Http\Server\MiddlewareInterface;

interface MiddlewaresInterface
{
    /**
     * Adds middleware(s) to the store.
     *
     * Accepts a single middleware or multiple middlewares
     *
     * @param string|array|Closure|MiddlewareInterface $middleware
     *
     * @return static
     */
    public function middleware(string|array|Closure|MiddlewareInterface $middleware): static;

    /**
     * Returns a MiddlewareStoreInterface instance that manages the middlewares.
     *
     * @return MiddlewareStoreInterface
     */
    public function getMiddlewares(): MiddlewareStoreInterface;
}
