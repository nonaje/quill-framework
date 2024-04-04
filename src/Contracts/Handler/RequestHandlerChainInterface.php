<?php

declare(strict_types=1);

namespace Quill\Contracts\Handler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerChainInterface
{
    public function enchain(MiddlewareInterface $middleware): RequestHandlerChainInterface;

    public function setLastLink(RequestHandlerInterface $handler): RequestHandlerChainInterface;

    public function getLastLink(): RequestHandlerInterface;
}