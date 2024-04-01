<?php

declare(strict_types=1);

namespace Quill\Contracts;

interface ConfigurationInterface
{
    public function all(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function put(string $key, mixed $value): void;

}