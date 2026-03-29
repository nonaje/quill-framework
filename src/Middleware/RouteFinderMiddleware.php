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

class RouteFinderMiddleware implements MiddlewareInterface
{
    public function __construct(protected RouterInterface $router) { }

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

            return new Route(
                uri: $route->getUri(),
                method: $route->getMethod(),
                target: $route->getTarget(),
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
        if ($route->getMethod()->value !== strtoupper($request->getMethod())) {
            return [false, []];
        }

        $routeSegments = $this->normalizeSegments($route->getUri()->getPath());
        $requestSegments = $this->normalizeSegments($request->getUri()->getPath());

        if (count($routeSegments) !== count($requestSegments)) {
            return [false, []];
        }

        $params = [];

        foreach ($routeSegments as $index => $segment) {
            $candidate = $requestSegments[$index];

            if ($this->isParameterSegment($segment)) {
                $params[substr($segment, 1)] = $candidate;
                continue;
            }

            if ($segment !== $candidate) {
                return [false, []];
            }
        }

        return [true, $params];
    }

    /**
     * @return list<string>
     */
    private function normalizeSegments(string $path): array
    {
        $segments = array_values(array_filter(explode('/', $path), static fn (string $part): bool => $part !== ''));

        return array_map(static fn (string $segment): string => rawurldecode($segment), $segments);
    }

    private function isParameterSegment(string $segment): bool
    {
        return str_starts_with($segment, ':') && strlen($segment) > 1;
    }
}
