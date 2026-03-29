<?php

declare(strict_types=1);

namespace Quill\Factory\Middleware;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Container\ContainerInterface;

final readonly class StringMiddleware implements MiddlewareInterface
{
    private MiddlewareInterface $resolved;

    public function __construct(private readonly ContainerInterface $container, private string $alias)
    {
        $this->resolved = $this->resolveAlias();
    }

    private function resolveAlias(): MiddlewareInterface
    {
        $target = config("app.middlewares.$this->alias", false);

        if ($target === false) {
            throw new LogicException("Middleware alias '{$this->alias}' is not registered in app config");
        }

        if (is_callable($target)) {
            return new ClosureMiddleware($this->container, $target);
        }

        if (is_string($target) && class_exists($target) && is_a($target, MiddlewareInterface::class, true)) {
            return new $target($this->container);
        }

        throw new LogicException(
            "Middleware alias '{$this->alias}' must reference a Closure or a MiddlewareInterface implementation"
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->resolved->process($request, $handler);
    }
}
