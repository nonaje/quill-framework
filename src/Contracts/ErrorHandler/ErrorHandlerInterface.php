<?php

declare(strict_types=1);

namespace Quill\Contracts\ErrorHandler;

use ErrorException;
use Throwable;

interface ErrorHandlerInterface
{
    /**
     * Handles general errors within the application.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return never
     * @throws ErrorException
     */
    public function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): never;

    public function handleException(Throwable $exception): never;

    public function handleShutdown(): void;

    public function listen(): void;
}
