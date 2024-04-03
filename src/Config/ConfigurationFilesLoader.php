<?php

declare(strict_types=1);

namespace Quill\Config;

use InvalidArgumentException;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Support\Helpers\Path;

final readonly class ConfigurationFilesLoader
{
    public function __construct(
        private ConfigurationInterface $config,
        private array $files
    )
    {
    }

    public function __invoke(): void
    {
        foreach ($this->files as $filename) {
            $this->loadConfig($filename);
        }
    }

    private function loadConfig(string $filename = null): void
    {
        $filename ??= Path::applicationFile('config');

        if (!file_exists($filename)) {
            throw new InvalidArgumentException("File: $filename does not exists");
        }

        if (is_file($filename)) {
            $this->config->put(
                key: substr(basename($filename), 0, -4),
                value: require_once $filename
            );
        }

        if (is_dir($filename)) {
            foreach (scandir($filename) as $filename) {
                $this->config->put(
                    key: substr(basename($filename), 0, -4),
                    value: require_once $filename
                );
            }
        }
    }
}