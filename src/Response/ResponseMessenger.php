<?php

declare(strict_types=1);

namespace Quill\Response;

use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Response\ResponseMessengerInterface;

final class ResponseMessenger implements ResponseMessengerInterface
{
    public function send(ResponseInterface $response): void
    {
        $this->sendHeaders($response);
        $this->sendStatusCode($response);

        echo $response->getPsrResponse()->getBody()->getContents();
    }

    private function sendHeaders(ResponseInterface $response): void
    {
        $headers = $response->getPsrResponse()->getHeaders();
        $headers['Content-Type'] ??= 'application/json';

        foreach ($headers as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            if (is_int($key)) {
                header($value);
            }

            if (is_string($key)) {
                header("$key: $value");
            }
        }
    }

    private function sendStatusCode(ResponseInterface $response): void
    {
        http_response_code($response->getPsrResponse()->getStatusCode());
    }
}
