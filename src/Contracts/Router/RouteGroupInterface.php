<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

interface RouteGroupInterface extends MiddlewaresInterface
{
    /**
     * Returns all routes registered within the group.
     *
     * The routes within groups are "compiled" and returned as particular routes
     * with their full path and the middlewares, both individual and those inherited by the group.
     *
     * @return RouteInterface[]
     */
    public function routes(): array;
}
