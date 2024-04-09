<?php

declare(strict_types=1);

namespace Quill\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Enums\Http\HttpCode;
use Quill\Enums\Http\HttpHeader;
use Quill\Enums\Http\MimeType;
use Quill\Factory\Psr7\Psr7Factory;

class Response implements ResponseInterface
{
    public function __construct(
        private PsrResponseInterface $psrResponse
    )
    {
    }

    public function getPsrResponse(): PsrResponseInterface
    {
        return $this->psrResponse;
    }

    public function json(array $data): self
    {
        $content = json_encode($data);

        $stream = Psr7Factory::streamFactory()->createStream($content);

        $response = $this->getPsrResponse()
            ->withBody($stream)
            ->withHeader(HttpHeader::CONTENT_TYPE->value, MimeType::JSON->value);

        return $this->setPsrResponse($response);
    }

    public function code(HttpCode $code): self
    {
        $response = $this->psrResponse->withStatus($code->value);

        return $this->setPsrResponse($response);
    }

    private function setPsrResponse(PsrResponseInterface $response): self
    {
        $this->psrResponse = $response;

        return $this;
    }
}
