<?php

declare(strict_types=1);

namespace Quill\Contracts;

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouterInterface;

/**
 * Represents the core application interface that manages global middleware, error handling,
 * and the overall lifecycle of the application. Acts as a mixin for the RouterInterface, allowing
 * routing functionality to be integrated seamlessly.
 *
 * @mixin RouterInterface
 */
interface ApplicationInterface
{
    /**
     * Sets a custom error handler for the application.
     *
     * The error handler is invoked when an exception or error occurs during request processing.
     *
     * @param RequestHandlerInterface $errorHandler
     * @return ApplicationInterface
     */
//    public function setErrorHandler(RequestHandlerInterface $errorHandler): ApplicationInterface;

    /**
     * Boots the application.
     *
     * This method initializes the application, processing the incoming request through
     * all registered global and route middlewares, and sends the final response to the client.
     *
     * It should be called after registering all global middlewares and routes.
     *
     * @return never
     */
    public function up(): never;
}
