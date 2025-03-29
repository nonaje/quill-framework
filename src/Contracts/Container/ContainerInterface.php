<?php

declare(strict_types=1);

namespace Quill\Contracts\Container;

use Quill\Container\Exception\SingletonAlreadyRegisteredException;

/**
 * Interface for a container that extends the PSR-11 ContainerInterface.
 *
 * This interface provides methods for both reading and writing entries to the container.
 * It extends the PSR-11 interface to include functionality for registering
 * singleton services that can be resolved once and cached for subsequent use.
 */
interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    public function register(string $id, callable $resolver): ContainerInterface;

    /**
     * Registers a singleton entry in the container.
     *
     * A singleton is a service that will be resolved once and the same instance
     * will be returned on subsequent calls to the container.
     *
     * @param string $id
     * @param callable $resolver
     * @return ContainerInterface
     * @throws SingletonAlreadyRegisteredException
     *
     */
    public function singleton(string $id, callable $resolver): ContainerInterface;

    /**
     * May return Container Instance or resolved service
     *
     * @param string $id
     * @param callable $refreshed
     * @return object
     */
    public function refresh(string $id, callable $refreshed): object;
}
