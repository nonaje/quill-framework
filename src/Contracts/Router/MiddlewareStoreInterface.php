<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;
use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareStoreInterface
{
    public function add(string|array|Closure|MiddlewareInterface $middleware): self;

    public function reset(): self;

    public function all(): array;
}