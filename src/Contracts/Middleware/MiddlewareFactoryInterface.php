<?php

namespace Quill\Contracts\Middleware;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareFactoryInterface
{
    public function make(array|string|\Closure|MiddlewareInterface $middleware): MiddlewareInterface;
}