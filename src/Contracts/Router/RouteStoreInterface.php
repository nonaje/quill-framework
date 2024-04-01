<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use \Closure;
use Quill\Router\Route;

interface RouteStoreInterface
{
    public function add(Route $route): RouteInterface;

    public function addGroup(string $prefix, Closure $routes): RouteGroupInterface;

    public function remove(Route $route): bool;

    public function update(Route $route): bool;

    public function current(Route $route = null): null|RouteInterface;

    public function routes(): array;

    public function groups(): array;

    public function count(): int;
}