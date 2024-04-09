<?php

declare(strict_types=1);

namespace Quill\Response;

use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Response\ResponseMessengerInterface;
use Quill\Support\Traits\Singleton;

final class ResponseMessenger implements ResponseMessengerInterface
{
    use Singleton;

    protected function __construct() {}

    public function send(ResponseInterface $response): void
    {
        $this->sendHeaders($response);
        $this->sendBody($response);
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

    private function sendBody(ResponseInterface $response): void
    {
        echo $response->getPsrResponse()->getBody()->getContents();
    }
}
