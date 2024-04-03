<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use Quill\Contracts\Router\MiddlewareInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Factory\Middleware\MiddlewareFactory;

final class MiddlewareStore implements MiddlewareStoreInterface
{
    /** @var MiddlewareInterface[] $stack */
    private array $stack = [];

    public function add(string|array|Closure|MiddlewareInterface $middleware): self
    {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];

        foreach ($middlewares as $key => $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                continue;
            }

            $middlewares[$key] = MiddlewareFactory::createMiddleware($middleware);
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
}