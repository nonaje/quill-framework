<?php

declare(strict_types=1);

namespace Quill\Handler\Error;

use ErrorException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\ErrorHandler\ErrorHandlerInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Response\ResponseSenderInterface;
use Quill\Enums\Http\HttpCode;
use Quill\Enums\RequestAttribute;
use Throwable;

abstract class ErrorHandler implements ErrorHandlerInterface
{
    public bool $displayErrors = true;

    public bool $logErrors = true;

    public string $logFile = 'errors.log';

    /**
     * The error to be processed and transformed into a response
     *
     * @var Throwable
     */
    protected Throwable $error;

    public function __construct(
        protected readonly ResponseInterface $response,
        protected readonly ResponseSenderInterface $responseSender
    ) {
    }

    /** @ineritDoc */
    abstract protected function toResponse(): ResponseInterface;

    public function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): never
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public function handleException(Throwable $exception): never
    {
        $this->error = $exception;

        $this->log();
        $this->render();
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError(
                errno: HttpCode::SERVER_ERROR->value,
                errstr: $error['message'] ?? 'Server Error',
                errfile: $error['file'] ?? '',
                errline: $error['line'] ?? 0
            );

            $this->render();
        }

        ob_end_flush();
    }

    public function listen(): void
    {
        // PHP Error handling configuration
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '0');

        // Output buffering for output control
        ob_start();

        // Register handlers
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    private function log(): void
    {
        if ($this->logErrors) {
            // TODO: Log error<
//            error_log($this->error->getMessage() . PHP_EOL, 3, $this->logFile);
        }
    }

    private function render(): never
    {
        if (! $this->displayErrors) {
            $this->error = new ErrorException(
                message: $this->error->getMessage() ?: "An internal server error occurred.",
                code: $this->error->getCode() ?: HttpCode::SERVER_ERROR->value
            );
        }

        $this->responseSender->send($this->toResponse());
    }
}
