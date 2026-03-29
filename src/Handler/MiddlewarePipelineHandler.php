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
    private ?ServerRequestInterface $request = null;
    private ?RequestHandlerInterface $handler = null;

    /** @var MiddlewareInterface[] $middlewares */
    private array $middlewares = [];

    /** @ineritDoc */
    public function send(ServerRequestInterface $request): MiddlewarePipelineInterface
    {
        $pipeline = clone $this;
        $pipeline->request = $request;

        return $pipeline;
    }

    /** @inheritDoc */
    public function through(array $middlewares): MiddlewarePipelineInterface
    {
        $pipeline = clone $this;
        $pipeline->middlewares = array_values($middlewares);

        return $pipeline;
    }

    /** @inheritDoc */
    public function to(RequestHandlerInterface $handler): MiddlewarePipelineInterface
    {
        $pipeline = clone $this;
        $pipeline->handler = $handler;

        return $pipeline;
    }

    /** @ineritDoc */
    public function getResponse(): ResponseInterface
    {
        if ($this->request === null || $this->handler === null) {
            throw new \LogicException('Middleware pipeline is not fully configured.');
        }

        return $this->handle($this->request);
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }

    /** @inheritDoc */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->handler === null) {
            throw new \LogicException('Middleware pipeline requires a final handler.');
        }

        return $this->dispatch($request, $this->middlewares, $this->handler);
    }

    /**
     * @param list<MiddlewareInterface> $middlewares
     */
    private function dispatch(
        ServerRequestInterface $request,
        array $middlewares,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if ($middlewares === []) {
            return $handler->handle($request);
        }

        $middleware = array_shift($middlewares);

        return $middleware->process($request, new class ($middlewares, $handler) implements RequestHandlerInterface {
            /**
             * @param list<MiddlewareInterface> $middlewares
             */
            public function __construct(
                private readonly array $middlewares,
                private readonly RequestHandlerInterface $handler,
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                if ($this->middlewares === []) {
                    return $this->handler->handle($request);
                }

                $middleware = $this->middlewares[0];
                $remaining = array_slice($this->middlewares, 1);

                return $middleware->process($request, new self($remaining, $this->handler));
            }
        });
    }
}
