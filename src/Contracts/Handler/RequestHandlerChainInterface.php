<?php

declare(strict_types=1);

namespace Quill\Contracts\Handler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerChainInterface
{
    public function stack(MiddlewareInterface $middleware): RequestHandlerChainInterface;

    public function setLast(RequestHandlerInterface $handler): RequestHandlerChainInterface;

    public function getLast(): RequestHandlerInterface;
}