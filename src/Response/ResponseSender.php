<?php

declare(strict_types=1);

namespace Quill\Response;

use Psr\Http\Message\ResponseInterface;
use Quill\Contracts\Response\ResponseSenderInterface;
use Quill\Enums\Http\HttpHeader;
use Quill\Enums\Http\MimeType;

final class ResponseSender implements ResponseSenderInterface
{
    public function send(ResponseInterface $response): never
    {
        $this->sendHeaders($response);
        $this->sendBody($response);
        exit;
    }

    private function sendHeaders(ResponseInterface $response): void
    {
        $headers = $response->getHeaders();
        $headers[HttpHeader::CONTENT_TYPE->value] ??= MimeType::JSON->value;

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

        header(
            sprintf(
                'HTTP/%s %d',
                $response->getProtocolVersion(),
                $response->getStatusCode()
            )
        );
    }

    private function sendBody(ResponseInterface $response): void
    {
        echo $response->getBody()->getContents();
    }
}
