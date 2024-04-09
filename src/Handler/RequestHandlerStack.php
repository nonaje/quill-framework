<?php

declare(strict_types=1);

namespace Quill\Handler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Handler\RequestHandlerChainInterface;
use LogicException;
use Quill\Factory\Middleware\RequestHandlerFactory;
use Quill\Support\Traits\Singleton;

final class RequestHandlerStack implements RequestHandlerChainInterface
{
    use Singleton;

    private null|RequestHandlerInterface $last = null;

    protected function __construct() {}

    public function stack(MiddlewareInterface $middleware): RequestHandlerChainInterface
    {
        if ($this->last === null) {
            throw new LogicException('Must specify the first link in the chain before creating a new one.');
        }

        $handler = RequestHandlerFactory::createRequestHandler($middleware, $this->getLast());

        $this->setLast($handler);

        return $this;
    }

    public function setLast(RequestHandlerInterface $handler): RequestHandlerChainInterface
    {
        $this->last = $handler;

        return $this;
    }

    public function getLast(): RequestHandlerInterface
    {
        return $this->last;
    }
}