<?php

declare(strict_types=1);

namespace Quill\Contracts\ErrorHandler;

use ErrorException;

interface ErrorHandlerInterface
{
    public bool $displayErrors { get; set; }

    public bool $logErrors { get; set; }

    public string $logFile { get; set; }

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
}
