<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;
use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareStoreInterface
{
    /**
     * Adds a middleware or multiple middlewares to the store.
     *
     * This method ensures that the middleware is properly stored for later processing.
     *
     * @param string|array|Closure|MiddlewareInterface $middleware Middleware to add.
     *
     * @return MiddlewareStoreInterface
     */
    public function add(string|array|Closure|MiddlewareInterface $middleware): MiddlewareStoreInterface;

    /**
     * Resets the middleware store, clearing all stored middlewares.
     *
     * This method should be used when the middleware collection needs
     * to be emptied and reinitialized.
     *
     * @return MiddlewareStoreInterface
     */
    public function reset(): MiddlewareStoreInterface;

    /**
     * Retrieves all middlewares from the store.
     *
     * Returns an array containing all stored middlewares. The order of the middlewares
     * should reflect the order in which they were added.
     *
     * @return array
     */
    public function all(): array;
}
