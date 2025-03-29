<?php

declare(strict_types=1);

namespace Quill\Response;

use Quill\Contracts\Response\ResponseInterface;
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
        $psrResponse = $response->getPsrResponse();
        $headers = $psrResponse->getHeaders();
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
                $psrResponse->getProtocolVersion(),
                $psrResponse->getStatusCode()
            )
        );
    }

    private function sendBody(ResponseInterface $response): void
    {
        echo $response->getPsrResponse()->getBody()->getContents();
    }
}
