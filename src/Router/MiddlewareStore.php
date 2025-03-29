<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Factory\Middleware\ClosureMiddleware;
use Quill\Factory\Middleware\StringClassMiddleware;
use Quill\Factory\Middleware\StringMiddleware;

final class MiddlewareStore implements MiddlewareStoreInterface
{
    /** @var MiddlewareInterface[] $stack */
    private array $stack = [];

    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function add(string|array|Closure|MiddlewareInterface $middleware): self
    {
        $middlewares = array_flatten([$middleware]);

        foreach ($middlewares as $key => $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                continue;
            }

            $middlewares[$key] = $this->makeMiddleware($middleware);
        }

        $this->stack = array_merge($this->stack, $middlewares);

        return $this;
    }

    public function reset(): self
    {
        $this->stack = [];

        return $this;
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function all(): array
    {
        return $this->stack;
    }

    private function makeMiddleware(string|Closure $middleware): MiddlewareInterface
    {
        return match (true) {
            is_callable($middleware) => new ClosureMiddleware($this->container, $middleware),

            class_exists($middleware) => new StringClassMiddleware($this->container, $middleware),

            is_string($middleware) => new StringMiddleware($this->container, $middleware),

            default => throw new InvalidArgumentException('Please provide a valid middleware type for creation'),
        };
    }
}
