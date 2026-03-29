<?php

declare(strict_types=1);

namespace Quill\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Middleware\MiddlewarePipelineInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;

class ExecuteGlobalUserDefinedMiddlewares implements MiddlewareInterface
{
    public function __construct(
        protected MiddlewarePipelineInterface $pipeline,
        private readonly MiddlewareStoreInterface $middlewares
    ) {
    }

    public function middlewareStore(): MiddlewareStoreInterface
    {
        return $this->middlewares;
    }

    /**
     * Processes the global user defined middlewares
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->pipeline
            ->send($request)
            ->through($this->middlewares->all())
            ->to($handler)
            ->getResponse();
    }
}
