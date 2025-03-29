<?php

declare(strict_types=1);

namespace Quill\Contracts\Response;

interface ResponseSenderInterface
{
    /**
     * Sends the HTTP response to the client.
     *
     * This method must handle the HTTP headers and response body,
     * ensuring that the output is properly transmitted to the HTTP client.
     *
     * @param ResponseInterface $response
     *
     * @return never
     */
    public function send(ResponseInterface $response): never;
}
