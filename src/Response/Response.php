<?php

declare(strict_types=1);

namespace Quill\Response;

use Exception;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Enums\Http\HttpCode;
use Quill\Enums\Http\HttpHeader;
use Quill\Enums\Http\MimeType;

class Response implements ResponseInterface, PsrResponseInterface
{
    /** @inheritDoc */
    public function plain(string $plain): PsrResponseInterface
    {
        return $this->body($plain, MimeType::PLAIN_TEXT);
    }

    /**
     * Return the updated instance of the psr response with the new body and Content-Type header
     *
     * @param string $content
     * @param MimeType $mime
     * @return PsrResponseInterface
     */
    private function body(string $content, MimeType $mime): PsrResponseInterface
    {
        return $this->getPsrResponse()
            ->withBody(Psr7Factory::streamFactory()->createStream($content))
            ->withHeader(HttpHeader::CONTENT_TYPE->value, $mime->value);
    }

    /** @inheritDoc */
    public function json(array $data): PsrResponseInterface
    {
        return $this->setPsrResponse(
            $this->body(json_encode($data), MimeType::JSON)
        );
    }

    /**
     * @throws Exception
     */
    public function view(string $view): PsrResponseInterface
    {
        //Checks if the received argument is the name of a html file inside the 'views' folder
        if (file_exists($path = $view) ||
            file_exists($path = file_path($view)) ||
            file_exists($path = file_path($view . '.html'))
        ) {
            $html = file_get_contents($path);
            return $this->html($html);
        }

        throw new Exception('The view "' . $view . '" does not exist or is not readable.');
    }

    /** @inheritDoc */
    public function html(string $html): PsrResponseInterface
    {
        return $this->body($html, MimeType::HTML);
    }

    /** @inheritDoc */
    public function code(HttpCode $code): PsrResponseInterface
    {
        return $this->withStatus($code->value);
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        // TODO: Implement getProtocolVersion() method.
    }

    /**
     * @param string $version
     * @return MessageInterface
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        // TODO: Implement withProtocolVersion() method.
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        // TODO: Implement getHeaders() method.
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        // TODO: Implement hasHeader() method.
    }

    /**
     * @param string $name
     * @return array
     */
    public function getHeader(string $name): array
    {
        // TODO: Implement getHeader() method.
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        // TODO: Implement getHeaderLine() method.
    }

    /**
     * @param string $name
     * @param $value
     * @return MessageInterface
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        // TODO: Implement withHeader() method.
    }

    /**
     * @param string $name
     * @param $value
     * @return MessageInterface
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        // TODO: Implement withAddedHeader() method.
    }

    /**
     * @param string $name
     * @return MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface
    {
        // TODO: Implement withoutHeader() method.
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        // TODO: Implement getBody() method.
    }

    /**
     * @param StreamInterface $body
     * @return MessageInterface
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        // TODO: Implement withBody() method.
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        // TODO: Implement getStatusCode() method.
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return PsrResponseInterface
     */
    public function withStatus(int $code, string $reasonPhrase = ''): PsrResponseInterface
    {
        // TODO: Implement withStatus() method.
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        // TODO: Implement getReasonPhrase() method.
    }
}
