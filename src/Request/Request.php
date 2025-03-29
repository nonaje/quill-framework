<?php

declare(strict_types=1);

namespace Quill\Request;

use Psr\Http\Message\ServerRequestInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Enums\Http\HttpMethod;
use Quill\Enums\RequestAttribute;

readonly class Request implements RequestInterface
{
    public function __construct(protected ServerRequestInterface $psrRequest)
    {
    }

    /** @ineritDoc */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->getRoute()->params()[$key] ?? $default;
    }

    private function getRoute(): RouteInterface
    {
        return $this->getPsrRequest()->getAttribute(RequestAttribute::ROUTE->value);
    }

    /** @ineritDoc */
    public function getPsrRequest(): ServerRequestInterface
    {
        return $this->psrRequest;
    }

    /** @ineritDoc */
    public function method(): HttpMethod
    {
        return HttpMethod::from(strtoupper($this->getPsrRequest()->getMethod()));
    }

    /** @ineritDoc */
    public function all(): mixed
    {
        // TODO: Implement method

        return [];
    }

    /** @ineritDoc */
    public function query(string $key, mixed $default = null): mixed
    {
        // TODO: Implement method

        $value = $default;

        return $value;
    }

    private function json(string $key, mixed $default): mixed
    {
        $body = json_decode($this->getPsrRequest()->getBody()->getContents(), true) ?? $default;

        return $key ? ($body[$key] ?? $default) : $body;
    }
}
