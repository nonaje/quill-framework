<?php

declare(strict_types=1);

namespace Quill\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Enums\RequestAttribute;
use Quill\Exception\RouteNotFoundException;
use Quill\Router\Route;

final readonly class FindRouteMiddleware implements MiddlewareInterface
{
    public function __construct(private RouterInterface $router) { }

    /**
     * Processes the incoming server request to find the matching route and pass it to the handler.
     *
     * This method attempts to find a route that matches the incoming request. If found, it attaches
     * the route as an attribute to the request and passes the modified request to the next handler.
     *
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->find($request);

        return $handler->handle(
            request: $request->withAttribute(RequestAttribute::ROUTE->value, $route)
        );
    }

    /**
     * Finds a matching route for the given server request.
     *
     * This private method iterates through the registered routes in the router and checks if any
     * of them match the incoming request. If a matching route is found, it clears the registered
     * routes and returns the found route with the appropriate parameters.
     *
     * @throws RouteNotFoundException
     */
    private function find(ServerRequestInterface $request): RouteInterface
    {
        foreach ($this->router->routes() as $route) {
            [$match, $params] = $this->resolveRoute($route, $request);

            if (!$match) {
                continue;
            }

            // Clears registered routes to free up memory once a match is found
            $this->router->clear();

            return new Route(
                uri: $route->uri(),
                method: $route->method(),
                target: $route->target(),
                middlewares: $route->getMiddlewares(),
                params: $params
            );
        }

        throw new RouteNotFoundException();
    }

    /**
     * Resolves whether the given route matches the server request.
     *
     * This method compares the route's URI and HTTP method with the incoming request. If the method
     * and the number of URI segments match, it further checks for parameterized matches. If the route
     * matches exactly or with parameters, it returns an array indicating a match and any extracted parameters.
     *
     * @return array<bool, array<string, string>> An array where the first element is a boolean indicating a match
     * and the second is an associative array of parameters.
     */
    private function resolveRoute(RouteInterface $route, ServerRequestInterface $request): array
    {
        // Check if the HTTP method matches
        if ($route->method()->value !== strtoupper($request->getMethod())) {
            return [false, []];
        }

        $routeParts = array_values(array_filter(explode('/', $route->uri()->__toString())));
        $wantedParts = array_values(array_filter(explode('/', $request->getUri()->getPath())));

        // Check if the number of URI segments match
        if (count($routeParts) !== count($wantedParts)) {
            return [false, []];
        }

        // Check for exact match
        if (count(array_diff($routeParts, $wantedParts)) === 0) {
            return [true, []];
        }

        // Check for parameterized match
        $routeCandidateParams = [];
        $params = [];
        foreach ($routeParts as $key => $part) {
            $routeCandidateParams[$key] = $part;
            $wantedPart = $wantedParts[$key];

            if (str_starts_with($part, ':') && isset($wantedPart)) {
                $routeCandidateParams[$key] = $wantedPart;
                // Delete the ":" character
                $params[substr($part, 1)] = $wantedPart;
            }
        }

        if (count(array_diff($routeCandidateParams, $wantedParts)) === 0) {
            return [true, $params];
        }

        return [false, []];
    }
}
