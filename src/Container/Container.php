<?php

declare(strict_types=1);

namespace Quill\Container;

use Quill\Container\Exception\CircularDependencyException;
use Quill\Container\Exception\ServiceNotFoundException;
use Quill\Container\Exception\SingletonAlreadyRegisteredException;
use Quill\Contracts\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * All registered bindings.
     *
     * @var array<string, Binding>
     */
    protected array $bindings = [];

    /** @var list<string> */
    private array $resolving = [];

    public function __construct(private readonly ?ContainerInterface $parent = null)
    {
    }

    /** @inheritDoc */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || ($this->parent?->has($id) ?? false);
    }

    /** @inheritDoc */
    public function register(string $id, callable $resolver): ContainerInterface
    {
        return $this->bind($id, $resolver, singleton: false);
    }

    /** @inheritDoc */
    public function singleton(string $id, callable $resolver): ContainerInterface
    {
        if (isset($this->bindings[$id]) && $this->bindings[$id]->isSingleton()) {
            throw new SingletonAlreadyRegisteredException($id);
        }

        return $this->bind($id, $resolver, singleton: true);
    }

    /** @inheritDoc */
    public function refresh(string $id, callable $refreshed): object
    {
        if (!isset($this->bindings[$id])) {
            throw new ServiceNotFoundException($id);
        }

        $singleton = $this->bindings[$id]->isSingleton();
        $this->bind($id, $refreshed, $singleton);

        return $this->get($id);
    }

    /** @inheritDoc */
    public function get(string $id): mixed
    {
        if (isset($this->bindings[$id])) {
            return $this->resolve($id);
        }

        if ($this->parent) {
            return $this->parent->get($id);
        }

        throw new ServiceNotFoundException($id);
    }

    private function resolve(string $id): mixed
    {
        if (in_array($id, $this->resolving, true)) {
            throw new CircularDependencyException([...$this->resolving, $id]);
        }

        $this->resolving[] = $id;

        try {
            return $this->bindings[$id]->getInstance($this);
        } finally {
            array_pop($this->resolving);
        }
    }

    /**
     * Binds a service or singleton to the container.
     *
     * @param string $id
     * @param callable $resolver
     * @param bool $singleton
     *
     * @return ContainerInterface
     */
    private function bind(string $id, callable $resolver, bool $singleton): ContainerInterface
    {
        $this->bindings[$id] = new Binding($resolver, $singleton);

        return $this;
    }
}
