<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;
use Psr\Http\Message\UriInterface;
use Quill\Enums\Http\HttpMethod;

interface RouteInterface
{
    public UriInterface $uri {
        get;
    }

    public HttpMethod $method {
        get;
    }

    public Closure|array|string $target {
        get;
    }

    public array $middlewares {
        get;
    }

    public array $params {
        get;
    }
}
