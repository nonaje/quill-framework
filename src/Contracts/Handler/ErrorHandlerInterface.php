<?php

declare(strict_types=1);

namespace Quill\Contracts\Handler;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface ErrorHandlerInterface
{
    public function captureException(Throwable $e): ResponseInterface;

    public function captureError(
        int $errorCode,
        string $errorDescription,
        string $filename = null,
        int $line = null,
        array $context = null
    ): ResponseInterface;
}