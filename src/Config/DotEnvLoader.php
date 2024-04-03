<?php

declare(strict_types=1);

namespace Quill\Config;

use InvalidArgumentException;
use Quill\Contracts\Configuration\ConfigurationInterface;

final readonly class DotEnvLoader
{
    public function __construct(
        private ConfigurationInterface $config,
        private string $filename
    )
    {
    }

    public function __invoke(): void
    {
        if (!file_exists($this->filename)) {
            throw new InvalidArgumentException("File: {$this->filename} does not exists");
        }

        // Load .env into configuration items
        $env = parse_ini_file($this->filename);
        $this->config->put('env', array_combine(array_map('strtolower', array_keys($env)), array_values($env)));
    }
}