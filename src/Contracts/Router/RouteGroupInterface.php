<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;

interface RouteGroupInterface
{
    /** @return RouteInterface[] */
    public function routes(): array;

    public function middleware(string|array|Closure|MiddlewareInterface $middleware): self;

    public function getMiddlewares(): MiddlewareStoreInterface;
}