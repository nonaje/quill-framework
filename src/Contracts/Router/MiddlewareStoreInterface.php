<?php

namespace Quill\Contracts\Router;

use Closure;

interface MiddlewareStoreInterface
{
    public function add(string|array|Closure|MiddlewareInterface $middleware): self;

    public function reset(): self;

    public function all(): array;
}