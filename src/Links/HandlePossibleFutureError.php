<?php

declare(strict_types=1);

namespace Quill\Links;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final readonly class HandlePossibleFutureError implements MiddlewareInterface
{
    public function __construct(private ErrorHandlerInterface $error)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            return $this->error->capture($e);
        }
    }
}