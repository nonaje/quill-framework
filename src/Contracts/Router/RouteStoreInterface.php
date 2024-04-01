<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Quill\Router\Route;

interface RouteStoreInterface
{
    public function add(Route $route): Route;

    public function remove(Route $route): bool;

    public function update(Route $route): bool;

    public function current(Route $route = null): null|Route;

    public function routes(): array;

    public function count(): int;
}