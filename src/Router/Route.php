<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Enum\HttpMethod;
use Closure;

readonly final class Route
{
    public string $uri;

    private function __construct(
        string $uri,
        public string $method,
        public Closure|array $target,
        public array $params,
        public Closure|string|array $middlewares
    ) {
        $this->uri = str_starts_with($uri, '/') ? $uri : '/' . $uri;
    }

    public static function make(
        string $uri,
        string $method,
        Closure|array $target,
        array $params = [],
        Closure|string|array $middlewares = []
    ): self {
        return new self(
            uri: $uri,
            method: HttpMethod::{strtoupper($method)}->value,
            target: $target,
            params: $params,
            middlewares: $middlewares
        );
    }
}
