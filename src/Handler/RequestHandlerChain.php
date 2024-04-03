<?php

namespace Quill\Handler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Handler\RequestHandlerChainInterface;
use LogicException;
use Quill\Factory\Middleware\RequestHandlerFactory;

class RequestHandlerChain implements RequestHandlerChainInterface
{
    private null|RequestHandlerInterface $link = null;

    public function enchain(MiddlewareInterface $middleware): RequestHandlerChainInterface
    {
        if ($this->link === null) {
            throw new LogicException('Must specify the first link in the chain before creating a new one.');
        }

        $this->setLink(
            RequestHandlerFactory::createRequestHandler($middleware, $this->getLink())
        );

        return $this;
    }

    public function setLink(RequestHandlerInterface $handler): RequestHandlerChainInterface
    {
        $this->link = $handler;

        return $this;
    }

    public function getLink(): RequestHandlerInterface
    {
        return $this->link;
    }
}