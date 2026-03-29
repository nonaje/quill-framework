<?php

declare(strict_types=1);

namespace Quill\Handler;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Enums\RequestAttribute;
use Quill\Request\Request;
use Quill\Router\Route;

final class RequestHandler implements RequestHandlerInterface
{
    public function __construct(private readonly ContainerInterface $container) { }

    /**
     * @throws ContainerExceptionInterface
     */
    public function handle(ServerRequestInterface $request): PsrResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = ($this->resolveRouteTarget($request))();

        return $response;
    }

    private function resolveRouteTarget(ServerRequestInterface $request): callable
    {
        /** @var RouteInterface $route */
        $route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $target = $route->getTarget();

        return match (true) {
            is_string($target) => $this->resolveStringTarget($request),
            is_array($target) => $this->resolveArrayTarget($request),
            is_callable($target) => $this->resolveCallableTarget($request),
            default => throw new LogicException('It is not possible to determine the target of the route'),
        };
    }

    private function resolveStringTarget(ServerRequestInterface $request): callable
    {
        /** @var RouteInterface $route */
        $route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $toResolve = explode('@', (string) $route->getTarget());
        $controller = $toResolve[0];
        $method = $toResolve[1] ?? '__invoke';
        $quillRequest = new Request($request);
        $response = $this->container->get(ResponseInterface::class);

        return fn() => new $controller($this->container, $quillRequest, $response)->{$method}(...array_values($route->getParams()));
    }

    private function resolveArrayTarget(ServerRequestInterface $request): callable
    {
        /** @var RouteInterface $route */
        $route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $target = $route->getTarget();
        $controller = $target[0];
        $method = $target[1] ?? '__invoke';
        $quillRequest = new Request($request);
        $response = $this->container->get(ResponseInterface::class);

        return fn() => new $controller($this->container, $quillRequest, $response)->{$method}(...array_values($route->getParams()));
    }

    private function resolveCallableTarget(ServerRequestInterface $request): callable
    {
        /** @var RouteInterface $route */
        $route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $quillRequest = new Request($request);
        $response = $this->container->get(ResponseInterface::class);

        return fn() => ($route->getTarget())($quillRequest, $response, $route->getParams());
    }
}
