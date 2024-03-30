<?php

namespace Quill\Contracts;

use Closure;
use Quill\Enum\HttpMethod;

interface RouteInterface
{
    public function middleware(Closure|string|array $middleware): self;

    public function uri(): string;

    public function method(): HttpMethod;

    public function target(): Closure|array;

    public function params(): array;

    public function middlewares(): array;

}