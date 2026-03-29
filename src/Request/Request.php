<?php

declare(strict_types=1);

namespace Quill\Request;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Enums\Http\HttpMethod;
use Quill\Enums\RequestAttribute;

class Request implements RequestInterface
{
    private ?array $cachedBodyInput = null;
    private bool $bodyResolved = false;
    private ?string $rawBody = null;

    public function __construct(private readonly ServerRequestInterface $psrRequest)
    {
    }

    public function getPsrRequest(): ServerRequestInterface
    {
        return $this->psrRequest;
    }

    /** @ineritDoc */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->getRoute()->getParams()[$key] ?? $default;
    }

    private function getRoute(): RouteInterface
    {
        return $this->psrRequest->getAttribute(RequestAttribute::ROUTE->value);
    }

    /** @ineritDoc */
    public function method(): HttpMethod
    {
        return HttpMethod::from(strtoupper($this->psrRequest->getMethod()));
    }

    /** @ineritDoc */
    public function all(): array
    {
        return array_replace_recursive($this->psrRequest->getQueryParams(), $this->body());
    }

    /** @ineritDoc */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->psrRequest->getQueryParams()[$key] ?? $default;
    }

    /** @ineritDoc */
    public function body(?string $key = null, mixed $default = null): mixed
    {
        $payload = $this->resolveBodyPayload();

        if ($key === null) {
            return $payload;
        }

        return $payload[$key] ?? $default;
    }

    private function resolveBodyPayload(): array
    {
        if ($this->bodyResolved) {
            return $this->cachedBodyInput ?? [];
        }

        $parsedBody = $this->psrRequest->getParsedBody();

        if (is_array($parsedBody) || is_object($parsedBody)) {
            $this->cachedBodyInput = $this->normalizeBodyInput($parsedBody);
            $this->bodyResolved = true;

            return $this->cachedBodyInput;
        }

        $decoded = json_decode($this->getRawBody(), true);

        $this->cachedBodyInput = is_array($decoded) ? $decoded : [];
        $this->bodyResolved = true;

        return $this->cachedBodyInput;
    }

    private function normalizeBodyInput(array|object $input): array
    {
        if (is_object($input)) {
            $input = get_object_vars($input);
        }

        foreach ($input as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $input[$key] = $this->normalizeBodyInput($value);
            }
        }

        return $input;
    }

    private function getRawBody(): string
    {
        if ($this->rawBody !== null) {
            return $this->rawBody;
        }

        $this->rawBody = $this->readBody($this->psrRequest->getBody());

        return $this->rawBody;
    }

    private function readBody(StreamInterface $stream): string
    {
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $contents = $stream->getContents();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $contents;
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->psrRequest, $name)) {
            return $this->psrRequest->{$name}(... $arguments);
        }

        throw new \BadMethodCallException();
    }
}
