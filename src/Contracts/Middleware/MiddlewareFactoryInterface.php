<?php

declare(strict_types=1);

namespace Quill\Contracts\Middleware;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareFactoryInterface
{
    public function make(array|string|\Closure|MiddlewareInterface $middleware): MiddlewareInterface;
}
