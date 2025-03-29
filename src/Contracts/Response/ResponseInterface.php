<?php

declare(strict_types=1);

namespace Quill\Contracts\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Quill\Enums\Http\HttpCode;

interface ResponseInterface
{
    /**
     * Returns the PSR-7 response interface
     *
     * @return PsrResponseInterface
     */
    public function getPsrResponse(): PsrResponseInterface;

    /**
     * Set the specified http code in the psr response
     *
     * @param HttpCode $code
     * @return ResponseInterface
     */
    public function code(HttpCode $code): ResponseInterface;

    /**
     * Set the response body as Json
     *
     * @param array $data
     * @return ResponseInterface
     */
    public function json(array $data): ResponseInterface;

    /**
     * Set the response body as plain text
     *
     * @param string $plain
     * @return ResponseInterface
     */
    public function plain(string $plain): ResponseInterface;

    /**
     * Set the response body as HTML
     *
     * @param string $html
     * @return ResponseInterface
     */
    public function html(string $html): ResponseInterface;
}
