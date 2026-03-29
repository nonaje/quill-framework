<?php

declare(strict_types=1);

namespace Quill\Contracts\Request;

use Psr\Http\Message\ServerRequestInterface;
use Quill\Enums\Http\HttpMethod;

interface RequestInterface
{
    public function getPsrRequest(): ServerRequestInterface;

    public function method(): HttpMethod;

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
    public function all(): array;

    /**
     * Retrieves data from the request body.
     *
     * When no key is provided the full parsed body is returned. For JSON requests the body is
     * decoded without consuming the stream, allowing multiple reads within the same request.
     */
    public function body(?string $key = null, mixed $default = null): mixed;
}
