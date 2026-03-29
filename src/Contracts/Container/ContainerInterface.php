<?php

declare(strict_types=1);

namespace Quill\Contracts\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Quill\Container\Exception\SingletonAlreadyRegisteredException;

/**
 * Interface for a container that extends the PSR-11 ContainerInterface.
 *
 * This interface provides methods for both reading and writing entries to the container.
 * It extends the PSR-11 interface to include functionality for registering
 * singleton services that can be resolved once and cached for subsequent use.
 */
interface ContainerInterface extends PsrContainerInterface
{
    /** @inheritDoc */
    public function get(string $id): mixed;

    /** @inheritDoc */
    public function has(string $id): bool;

    public function register(string $id, callable $resolver): ContainerInterface;

    public function singleton(string $id, callable $resolver): ContainerInterface;

    public function refresh(string $id, callable $refreshed): object;
}
