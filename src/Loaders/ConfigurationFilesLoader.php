<?php

declare(strict_types=1);

namespace Quill\Loaders;

use InvalidArgumentException;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Support\Helpers\Path;

final readonly class ConfigurationFilesLoader implements FilesLoader
{
    public function __construct(
        private ConfigurationInterface $config,
    )
    {
    }

    public function loadFiles(array $filenames): void
    {
        foreach ($filenames as $filename) {
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