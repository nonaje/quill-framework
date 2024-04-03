<?php

namespace Quill\Contracts\Handler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerChainInterface
{
    public function enchain(MiddlewareInterface $middleware): RequestHandlerChainInterface;

    public function setLink(RequestHandlerInterface $handler): RequestHandlerChainInterface;

    public function getLink(): RequestHandlerInterface;
}