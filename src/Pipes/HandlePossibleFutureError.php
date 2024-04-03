<?php

declare(strict_types=1);

namespace Quill\Pipes;

use Closure;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Throwable;

final readonly class HandlePossibleFutureError
{
    public function __construct(private ErrorHandlerInterface $handler)
    {
    }

    public function __invoke(RequestInterface $request, Closure $next): ResponseInterface
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            $this->handler->capture($e);
        }
    }
}