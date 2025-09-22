<?php

declare(strict_types=1);

namespace Quill\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Enums\RequestAttribute;
use Throwable;

final readonly class ExceptionHandlingMiddleware implements MiddlewareInterface
{
    public function __construct(private RequestHandlerInterface $errorHandler)
    {
    }

    /**
     * Processes an incoming server request and returns a response, handling exceptions.
     *
     * If an exception is thrown during the request handling, it catches the exception and
     * forwards the request with the 'ERROR' parameter to the configured error handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            return $this->errorHandler->handle(
                request: $request->withAttribute(RequestAttribute::ERROR->value, $e)
            );
        }
    }
}
