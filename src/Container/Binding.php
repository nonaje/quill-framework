<?php

declare(strict_types=1);

namespace Quill\Container;

use Closure;
use Quill\Contracts\Container\ContainerInterface;

final class Binding
{
    private Closure $resolver;
    private bool $singleton;
    private mixed $instance = null;
    private bool $resolved = false;

    public function __construct(callable $resolver, bool $singleton)
    {
        $this->resolver = $resolver;
        $this->singleton = $singleton;
    }

    /**
     * Returns the service instance.
     *
     * @param ContainerInterface $container The dependency injection container
     *
     * @return mixed
     */
    public function getInstance(ContainerInterface $container): mixed
    {
        if ($this->singleton) {
            if (!$this->resolved) {
                $this->instance = ($this->resolver)($container);
                $this->resolved = true;
            }

            return $this->instance;
        }

        return ($this->resolver)($container);
    }

    /**
     * Checks if the binding is a singleton.
     *
     * @return bool
     */
    public function isSingleton(): bool
    {
        return $this->singleton;
    }
}
