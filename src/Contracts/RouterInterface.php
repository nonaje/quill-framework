<?php

namespace Quill\Contracts;

use Closure;

interface RouterInterface
{
    public function load(string $routePath): self;

    public function addRoute(string $method, string $uri, Closure|array $target): RouteInterface;

    public function dispatch(): void;
}