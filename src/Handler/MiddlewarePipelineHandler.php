<?php

declare(strict_types=1);

namespace Quill\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Middleware\MiddlewarePipelineInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MiddlewarePipelineHandler implements RequestHandlerInterface, MiddlewarePipelineInterface
{
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;

    /** @var MiddlewareInterface[] $middlewares */
    private array $middlewares;

    /** @ineritDoc */
    public function send(ServerRequestInterface $request): MiddlewarePipelineInterface
    {
        $this->request = $request;

        return $this;
    }

    /** @inheritDoc */
    public function through(array $middlewares): MiddlewarePipelineInterface
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /** @inheritDoc */
    public function to(RequestHandlerInterface $handler): MiddlewarePipelineInterface
    {
        $this->handler = $handler;

        return $this;
    }

    /** @ineritDoc */
    public function getResponse(): ResponseInterface
    {
        return $this->handle($this->request);
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }

    /** @inheritDoc */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middlewares)) {
            return $this->handler->handle($request);
        }

        return array_shift($this->middlewares)->process($request, $this);
    }
}
