<?php

declare(strict_types=1);

namespace Quill\Response;

use Psr\Http\Message\ResponseInterface;
use Quill\Contracts\Response\ResponseSenderInterface;
use Quill\Enums\Http\HttpHeader;
use Quill\Enums\Http\MimeType;

final class ResponseSender implements ResponseSenderInterface
{
    /** @var callable(string,bool,?int):void|null */
    private $headerEmitter;

    /** @var callable(string):void|null */
    private $bodyEmitter;

    /** @var callable():void|null */
    private $terminator;

    /**
     * @param callable(string,bool,?int):void|null $headerEmitter
     * @param callable(string):void|null $bodyEmitter
     * @param callable():void|null $terminator
     */
    public function __construct(
        ?callable $headerEmitter = null,
        ?callable $bodyEmitter = null,
        ?callable $terminator = null,
    ) {
        $this->headerEmitter = $headerEmitter;
        $this->bodyEmitter = $bodyEmitter;
        $this->terminator = $terminator;
    }

    public function send(ResponseInterface $response): void
    {
        $this->sendStatusLine($response);
        $this->sendHeaders($response);
        $this->sendBody($response);

        $terminator = $this->terminator ?? static function (): void {
            exit;
        };

        $terminator();
    }

    private function emitHeader(string $header, bool $replace = true, ?int $responseCode = null): void
    {
        $emitter = $this->headerEmitter ?? static function (string $header, bool $replace = true, ?int $responseCode = null): void {
            if ($responseCode === null) {
                header($header, $replace);
                return;
            }

            header($header, $replace, $responseCode);
        };

        $emitter($header, $replace, $responseCode);
    }

    private function sendStatusLine(ResponseInterface $response): void
    {
        $reason = $response->getReasonPhrase();
        $statusLine = sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $reason !== '' ? ' ' . $reason : ''
        );

        $this->emitHeader($statusLine, true, $response->getStatusCode());
    }

    private function sendHeaders(ResponseInterface $response): void
    {
        $headers = $response->getHeaders();
        $hasContentType = false;

        foreach (array_keys($headers) as $name) {
            if (is_string($name) && strcasecmp($name, HttpHeader::CONTENT_TYPE->value) === 0) {
                $hasContentType = true;
                break;
            }
        }

        if (!$hasContentType) {
            $headers[HttpHeader::CONTENT_TYPE->value] = MimeType::JSON->value . '; charset=utf-8';
        }

        foreach ($headers as $name => $values) {
            $values = is_array($values) ? $values : [$values];

            $replace = true;

            foreach ($values as $value) {
                if (is_string($name)) {
                    $this->emitHeader(sprintf('%s: %s', $name, $value), $replace);
                } else {
                    $this->emitHeader((string) $value, $replace);
                }

                $replace = false;
            }
        }
    }

    private function sendBody(ResponseInterface $response): void
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        $emitBody = $this->bodyEmitter ?? static function (string $contents): void {
            echo $contents;
        };

        $emitBody($body->getContents());
    }
}
