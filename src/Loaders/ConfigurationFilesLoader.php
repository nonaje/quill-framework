<?php

declare(strict_types=1);

namespace Quill\Loaders;

use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Exceptions\FileNotFoundException;
use Quill\Support\Traits\Singleton;

final class ConfigurationFilesLoader implements FilesLoader
{
    use Singleton;

    protected function __construct(
        private readonly ConfigurationInterface $config,
    )
    {
    }

    /**
     * @throws FileNotFoundException
     */
    public function loadFiles(string ...$filenames): void
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