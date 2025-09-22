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
        /** @var ResponseInterface|null $response */
        $response = ($this->resolveRouteTarget($request))();

        return $response;
    }

    private function resolveRouteTarget(ServerRequestInterface $request): callable
    {
        $route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $target = $route->target;

        return match (true) {
            is_string($target) => $this->resolveStringTarget($request),
            is_array($target) => $this->resolveArrayTarget($request),
            is_callable($target) => $this->resolveCallableTarget($request),
            default => throw new LogicException('It is not possible to determine the target of the route'),
        };
    }

    private function resolveStringTarget(\Psr\Http\Message\RequestInterface $request): callable
    {
        /** @var RouteInterface $route */
        $route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $toResolve = explode('@', $route->target);
        $controller = $toResolve[0];
        $method = $toResolve[1] ?? '__invoke';
        $response = $this->container->get(ResponseInterface::class);

        return fn() => new $controller($request, $response, $route->params)->{$method}();
    }

    private function resolveArrayTarget(\Psr\Http\Message\RequestInterface $request): callable
    {
        /** @var RouteInterface $route */
        $route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $controller = $route->target[0];
        $method = $route->target[1] ?? '__invoke';
        $response = $this->container->get(ResponseInterface::class);

        return fn() => new $controller($request, $response, $route->params)->{$method}();
    }

    private function resolveCallableTarget(\Psr\Http\Message\RequestInterface $request): callable
    {
        /** @var RouteInterface $route */
        $route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $response = $this->container->get(ResponseInterface::class);

        return fn() => ($route->target)($request, $response, $route->params);
    }
}
