<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;
use Quill\Enum\HttpMethod;
use Quill\Router\RouteMiddlewareStore;

interface RouteInterface
{
    public function middleware(string|array|Closure|MiddlewareInterface $middleware): self;

    public function uri(): string;

    public function method(): HttpMethod;

    public function target(): Closure|array;

    public function params(): array;

    public function middlewares(): RouteMiddlewareStore;
}