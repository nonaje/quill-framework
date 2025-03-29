<?php

declare(strict_types=1);

namespace Quill\Container;

use Exception;
use Quill\Container\Exception\ServiceNotFoundException;
use Quill\Container\Exception\SingletonAlreadyRegisteredException;
use Quill\Contracts\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * The container instance
     *
     * @var ContainerInterface
     */
    protected static ContainerInterface $_instance;

    /**
     * All registered bindings.
     *
     * @var array<string, Binding>
     */
    protected array $bindings = [];

    /**
     * Protected class constructor to prevent direct object creation.
     */
    protected function __construct()
    {
    }

    /**
     * To return new or existing Singleton instance of the class from which it is called.
     * As it sets to final it can't be overridden.
     *
     * @return ContainerInterface Singleton instance of the class.
     */
    public static function make(): ContainerInterface
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     * @throws Exception
     */
    final public function __wakeup()
    {
        throw new Exception('Cannot unserialize the container');
    }

    /** @inheritDoc */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
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
        if (!isset($this->bindings[$id])) {
            throw new ServiceNotFoundException($id);
        }

        return $this->bindings[$id]->getInstance($this);
    }

    /**
     * Prevent object cloning
     */
    private function __clone()
    {
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
