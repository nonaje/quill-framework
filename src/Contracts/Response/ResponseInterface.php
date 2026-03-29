<?php

declare(strict_types=1);

namespace Quill\Contracts\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Quill\Enums\Http\HttpCode;

interface ResponseInterface extends PsrResponseInterface
{
    /**
     * Set the specified http code in the psr response
     *
     * @param HttpCode $code
     * @return PsrResponseInterface
     */
    public function code(HttpCode $code): PsrResponseInterface;

    /**
     * Set the specified http code in the psr response.
     */
    public function status(HttpCode|int $code, string $reason = ''): PsrResponseInterface;

    /**
     * Set the response body as Json
     *
     * @param array $data
     * @return PsrResponseInterface
     */
    public function json(array $data): PsrResponseInterface;

    /**
     * Set the response body as plain text
     *
     * @param string $plain
     * @return PsrResponseInterface
     */
    public function plain(string $plain): PsrResponseInterface;

    /**
     * Set the response body as HTML
     *
     * @param string $html
     * @return PsrResponseInterface
     */
    public function html(string $html): PsrResponseInterface;

    /**
     * Apply the provided headers to the response.
     *
     * @param array<string, scalar|array<scalar>> $headers
     */
    public function headers(array $headers): PsrResponseInterface;
}
