<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

use Closure;
use Psr\Http\Message\UriInterface;
use Quill\Enums\Http\HttpMethod;

interface RouteInterface extends MiddlewaresInterface
{
    /**
     * Gets the uri path associated with this route.
     *
     * @return UriInterface The URI path for the route (e.g., "/users/{id}").
     */
    public function uri(): UriInterface;

    /**
     * Gets the HTTP method associated with this route.
     *
     * @return HttpMethod
     */
    public function method(): HttpMethod;

    /**
     * Gets the target handler for this route.
     *
     * The target defines the action to be executed when the route matches.
     *
     * @return Closure|array|string The route's target handler.
     */
    public function target(): Closure|array|string;

    /**
     * Gets the parameters extracted from the route's path.
     *
     * @return array An associative array of route parameters (e.g., ['id' => 42]).
     */
    public function params(): array;
}
