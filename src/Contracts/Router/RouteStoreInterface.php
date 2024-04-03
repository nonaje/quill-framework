<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Quill\Router\Route;

interface RouteStoreInterface
{
    public function add(Route $route): RouteInterface;

    public function addGroup(RouteGroupInterface $group): RouteGroupInterface;

    public function update(Route $route): bool;

    public function setMatchedRoute(Route $route): self;

    public function getMatchedRoute(): RouteInterface;

    /**
     * @return array<empty, empty>|RouteInterface[]
     */
    public function routes(): array;

    /**
     * @return array<empty, empty>|RouteGroupInterface[]
     */
    public function groups(): array;

    /**
     * Sum of the routes and the groups' routes
     * Returns a single level array
     *
     * @return array<empty, empty>|RouteInterface[]
     */
    public function all(): array;

    public function count(): int;
}