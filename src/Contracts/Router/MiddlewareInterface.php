<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;

interface MiddlewareInterface
{
    // TODO: Psr7 Implementation
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next): void;
}