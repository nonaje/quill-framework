<?php

declare(strict_types=1);

namespace Quill\Router;

use Quill\Contracts\MiddlewareInterface;
use \Closure;
use \LogicException;
use Quill\Factory\MiddlewareFactory;

final class RouteMiddlewareStore
{
    /** @var MiddlewareInterface[] $stack  */
    private array $stack = [];

    public function add(string|array|Closure|MiddlewareInterface $middleware): void
    {
        $this->stack[] = MiddlewareFactory::createMiddleware($middleware);
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function all(): array
    {
        return $this->stack;
    }
}