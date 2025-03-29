<?php

declare(strict_types=1);

namespace Quill\Contracts\Request;

use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface
{
    /**
     * Retrieves the underlying PSR-7 server request.
     *
     * @return ServerRequestInterface
     */
    public function getPsrRequest(): ServerRequestInterface;

    /**
     * Retrieves a parameter from the route's path or its default value if not present.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function route(string $key, mixed $default = null): mixed;

    /**
     * Retrieves a value from the request's query string or its default value if not present.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function query(string $key, mixed $default = null): mixed;

    /**
     * Retrieves all input data from the request.
     *
     * This may include data from query parameters, form data, or other sources.
     *
     * @return mixed
     */
    public function all(): mixed;
}
