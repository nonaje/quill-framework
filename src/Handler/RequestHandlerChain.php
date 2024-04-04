<?php

declare(strict_types=1);

namespace Quill\Handler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Handler\RequestHandlerChainInterface;
use LogicException;
use Quill\Factory\Middleware\RequestHandlerFactory;

class RequestHandlerChain implements RequestHandlerChainInterface
{
    private null|RequestHandlerInterface $lastLink = null;

    public function enchain(MiddlewareInterface $middleware): RequestHandlerChainInterface
    {
        if ($this->lastLink === null) {
            throw new LogicException('Must specify the first link in the chain before creating a new one.');
        }

        $handler = RequestHandlerFactory::createRequestHandler($middleware, $this->getLastLink());

        $this->setLastLink($handler);

        return $this;
    }

    public function setLastLink(RequestHandlerInterface $handler): RequestHandlerChainInterface
    {
        $this->lastLink = $handler;

        return $this;
    }

    public function getLastLink(): RequestHandlerInterface
    {
        return $this->lastLink;
    }
}