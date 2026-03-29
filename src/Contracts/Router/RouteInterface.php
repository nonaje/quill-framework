<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;
use Psr\Http\Message\UriInterface;
use Quill\Enums\Http\HttpMethod;

interface RouteInterface
{
    public function getUri(): UriInterface;

    public function getMethod(): HttpMethod;

    public function getTarget(): Closure|array|string;

    public function getMiddlewares(): array;

    public function getParams(): array;
}
