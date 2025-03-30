<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

interface RouteStoreInterface
{
    /**
     * Adds a new route to the store.
     *
     * @param RouteInterface $route
     * @return RouteInterface
     */
    public function add(RouteInterface $route): RouteInterface;

    /**
     * Clears all stored routes and route groups.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Retrieves all compiled routes, including those within groups.
     *
     * The routes from groups are expanded with their full paths and middlewares,
     * combining both individual and inherited middlewares from their groups.
     *
     * @return RouteInterface[]
     */
    public function all(): array;
}
