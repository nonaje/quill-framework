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

final class RequestHandler implements RequestHandlerInterface
{
    private ResponseInterface $response;
    private RequestInterface $request;
    private RouteInterface $route;

    /**
     * @throws ContainerExceptionInterface
     */
    public function handle(ServerRequestInterface $request): PsrResponseInterface
    {
        $this->route = $request->getAttribute(RequestAttribute::ROUTE->value);
        $this->response = resolve(ResponseInterface::class);
        $this->request = refresh(id: RequestInterface::class, refreshed: fn(ContainerInterface $c) => new Request(
            $request
        ));

        /** @var ResponseInterface|null $response */
        $response = ($this->resolveRouteTarget())();

        return is_null($response) ? $this->response->getPsrResponse() : $response->getPsrResponse();
    }

    private function resolveRouteTarget(): callable
    {
        return match (true) {
            is_string($this->route->target()) => $this->resolveStringTarget(),
            is_array($this->route->target()) => $this->resolveArrayTarget(),
            is_callable($this->route->target()) => $this->resolveCallableTarget(),
            default => throw new LogicException('It is not possible to determine the target of the route'),
        };
    }

    private function resolveStringTarget(): callable
    {
        $toResolve = explode('@', $this->route->target());
        $controller = $toResolve[0];
        $method = $toResolve[1] ?? '__invoke';

        return fn() => (new $controller($this->request, $this->response, $this->route->params()))->{$method}();
    }

    private function resolveArrayTarget(): callable
    {
        $controller = $this->route->target()[0];
        $method = $this->route->target()[1] ?? '__invoke';

        return fn() => (new $controller($this->request, $this->response, $this->route->params()))->{$method}();
    }

    private function resolveCallableTarget(): callable
    {
        return fn() => ($this->route->target())($this->request, $this->response, $this->route->params());
    }
}
