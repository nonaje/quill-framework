<?php

declare(strict_types=1);

namespace Quill\Router;

use Closure;
use LogicException;
use Psr\Http\Message\UriInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Enums\Http\HttpMethod;

class Route implements RouteInterface
{
    public function __construct(
        protected(set) UriInterface $uri,
        protected(set) HttpMethod $method,
        protected(set) Closure|array|string $target,
        protected(set) array $middlewares = [],
        protected(set) array $params = [],
    ) {
        $this->assert();
    }

    protected function assert(): void
    {
        if (!str_starts_with($this->uri->__toString(), '/')) {
            throw new LogicException("URI $this->uri must starts with '/'");
        }

        if (!$this->method instanceof HttpMethod) {
            throw new LogicException("Please provide a valid HTTP method for URI $this->uri");
        }

        if (!is_callable($this->target) && !is_array($this->target) && !is_string($this->target)) {
            throw new LogicException(
                'The route target must be of type array or callable, given ' . gettype($this->target)
            );
        }

        if (is_array($this->target)) {
            if (count($this->target) < 1) {
                throw new LogicException("The route target can't be an empty array");
            }

            $controller = $this->target[0];
            $method = $this->target[1] ?? '__invoke';

            if (!class_exists($controller)) {
                throw new LogicException("Please provide a valid controller class, provided: $controller");
            }

            if (!method_exists($controller, $method)) {
                throw new LogicException("Please provide a valid controller method, provided: $controller@$method");
            }
        }

        if (is_string($this->target)) {
            if (empty($this->target)) {
                throw new LogicException('Please provide a valid route target');
            }

            $data = explode('@', $this->target);
            $controller = $data[0];
            $method = $data[1] ?? '__invoke';

            if (!class_exists($controller)) {
                throw new LogicException("Please provide a valid controller class, provided: $controller");
            }

            if (!method_exists($controller, $method)) {
                throw new LogicException("Please provide a valid controller method, provided: $controller@$method");
            }
        }

        if (!is_array($this->params)) {
            throw new LogicException(
                'Invalid route params: ' . implode(', ', $this->params)
            );
        }
    }
}
