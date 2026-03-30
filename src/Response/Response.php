<?php

declare(strict_types=1);

namespace Quill\Response;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Enums\Http\HttpCode;
use Quill\Enums\Http\HttpHeader;
use Quill\Enums\Http\MimeType;

class Response implements ResponseInterface
{
    /** @var array<string, list<string>> */
    private array $headers = [];

    /** @var array<string, string> */
    private array $headerNames = [];

    private StreamInterface $body;

    private int $statusCode;

    private string $reasonPhrase;

    private string $protocolVersion;

    public function __construct(
        HttpCode $status = HttpCode::INTERNAL_SERVER_ERROR,
        array $headers = [],
        ?StreamInterface $body = null,
        string $version = '1.1',
        string $reason = ''
    ) {
        $this->statusCode = $status->value;
        $this->protocolVersion = $version;
        $this->reasonPhrase = $reason !== '' ? $reason : self::reasonPhraseFor($this->statusCode);
        $this->body = $body ?? Stream::create('');

        foreach ($headers as $name => $value) {
            $this->storeHeader($name, $value);
        }
    }

    public function plain(string $plain): self
    {
        return $this->body($plain, MimeType::PLAIN_TEXT);
    }

    public function json(array $data): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->body($json ?: 'null', MimeType::JSON);
    }

    public function html(string $html): self
    {
        return $this->body($html, MimeType::HTML);
    }

    /**
     * @throws Exception
     */
    public function view(string $view): self
    {
        if (file_exists($path = $view)
            || file_exists($path = $view . '.html')
        ) {
            $html = file_get_contents($path);
            if ($html === false) {
                throw new Exception('The view "' . $view . '" is not readable.');
            }
            return $this->html($html);
        }

        throw new Exception('The view "' . $view . '" does not exist or is not readable.');
    }

    public function code(HttpCode $code): self
    {
        return $this->status($code);
    }

    public function status(HttpCode|int $code, string $reason = ''): self
    {
        $statusCode = $code instanceof HttpCode ? $code->value : $code;

        return $this->withStatus($statusCode, $reason);
    }

    public function headers(array $headers): self
    {
        $response = $this;

        foreach ($headers as $name => $value) {
            if (!is_string($name)) {
                continue;
            }

            $response = is_array($value)
                ? $response->withHeader($name, array_map(static fn ($item) => (string) $item, $value))
                : $response->withHeader($name, (string) $value);
        }

        return $response;
    }

    private function body(string $content, MimeType $mime): self
    {
        $stream = Stream::create($content);

        return $this
            ->withHeader(HttpHeader::CONTENT_TYPE->value, $mime->value . '; charset=utf-8')
            ->withBody($stream);
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): PsrResponseInterface
    {
        $clone = clone $this;
        $clone->protocolVersion = (string) $version;

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        $normalized = strtolower((string) $name);

        return isset($this->headerNames[$normalized]);
    }

    public function getHeader($name): array
    {
        if (!$this->hasHeader($name)) {
            return [];
    }

        $original = $this->headerNames[strtolower((string) $name)];

        return $this->headers[$original] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader($name, $value): PsrResponseInterface
    {
        $this->assertHeaderName($name);
        $values = $this->normalizeHeaderValue($value);

        $clone = clone $this;
        $normalized = strtolower($name);
        $original = $clone->headerNames[$normalized] ?? null;

        if ($original !== null && $original !== $name) {
            unset($clone->headers[$original]);
        }

        $clone->headerNames[$normalized] = $name;
        $clone->headers[$name] = $values;

        return $clone;
    }

    public function withAddedHeader($name, $value): PsrResponseInterface
    {
        $this->assertHeaderName($name);
        $values = $this->normalizeHeaderValue($value);

        $clone = clone $this;
        $normalized = strtolower($name);
        $original = $clone->headerNames[$normalized] ?? $name;
        $clone->headerNames[$normalized] = $original;
        $clone->headers[$original] = array_merge($clone->headers[$original] ?? [], $values);

        return $clone;
    }

    public function withoutHeader($name): PsrResponseInterface
    {
        if (!$this->hasHeader($name)) {
            return clone $this;
        }

        $clone = clone $this;
        $normalized = strtolower((string) $name);
        $original = $clone->headerNames[$normalized];
        unset($clone->headerNames[$normalized], $clone->headers[$original]);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): PsrResponseInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): PsrResponseInterface
    {
        if (!is_int($code) || $code < 100 || $code > 599) {
            throw new InvalidArgumentException('Status code must be an integer between 100 and 599.');
        }

        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $reasonPhrase !== '' ? $reasonPhrase : self::reasonPhraseFor($code);

        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    private static function reasonPhraseFor(int $code): string
    {
        return self::STATUS_PHRASES[$code] ?? '';
    }

    private function storeHeader(string $name, mixed $value): void
    {
        $this->assertHeaderName($name);
        $values = $this->normalizeHeaderValue($value);
        $normalized = strtolower($name);
        $this->headerNames[$normalized] = $name;
        $this->headers[$name] = $values;
    }

    private function assertHeaderName(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Header name must be a non-empty string.');
        }
    }

    /**
     * @return list<string>
     */
    private function normalizeHeaderValue(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_map(static fn ($item) => (string) $item, $values));
    }

    private const STATUS_PHRASES = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];
}
