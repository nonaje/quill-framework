<?php

declare(strict_types=1);

namespace Quill\Loaders;

use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Exceptions\FileNotFoundException;
use Quill\Support\PathFinder\Path;

final readonly class ConfigurationFilesLoader implements FilesLoader
{
    public function __construct(
        private ConfigurationInterface $config,
    )
    {
    }

    /**
     * @throws FileNotFoundException
     */
    public function loadFiles(array $filenames): void
    {
        foreach ($filenames as $filename) {
            $this->loadConfig($filename);
        }
    }

    /**
     * @throws FileNotFoundException
     */
    private function loadConfig(string $filename = null): void
    {
        $filename ??= path()->applicationFile('config');

        if (!file_exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        if (is_file($filename)) {
            $this->config->put(
                key: substr(basename($filename), 0, -4),
                value: require_once $filename
            );

            return;
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