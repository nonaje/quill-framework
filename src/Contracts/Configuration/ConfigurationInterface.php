<?php

declare(strict_types=1);

namespace Quill\Contracts\Configuration;

interface ConfigurationInterface
{
    /**
     * Returns all configurations
     *
     * @return array
     */
    public function all(): array;

    /**
     * Returns the configuration requested by the key, if the key doesn't exist
     * then returns the default value
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Stores the value in the specified key, if the key already exists
     * then the current value will be replaced with the new one.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function put(string $key, mixed $value): ConfigurationInterface;

    /**
     * Stores the value in the specified key, if the key already exists
     * then an array will be created on that key storing the values.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function push(string $key, mixed $value): ConfigurationInterface;
}
