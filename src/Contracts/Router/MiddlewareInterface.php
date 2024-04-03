<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;
use Quill\Contracts\Request\RequestInterface;

interface MiddlewareInterface
{
    // TODO: Psr-15 Implementation
    public function handle(RequestInterface $request, Closure $next): void;
}