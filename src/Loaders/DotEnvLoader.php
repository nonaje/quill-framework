<?php

declare(strict_types=1);

namespace Quill\Loaders;

use InvalidArgumentException;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Support\Traits\Singleton;

final class DotEnvLoader implements FilesLoader
{
    use Singleton;

    protected function __construct(
        private readonly ConfigurationInterface $config
    )
    {
    }

    public function loadFiles(string ...$filenames): void
    {
        if (count($filenames) > 1) {
            throw new InvalidArgumentException('Only one dotenv file can be loaded.');
        }

        $filename = $filenames[0];

        if (! str_ends_with($filename, '.env')) {
            throw new InvalidArgumentException("File: {$filename} must be a .env file");
        }

        if (!file_exists($filename)) {
            throw new InvalidArgumentException("File: {$filename} does not exists");
        }

        // Load .env into configuration items
        $env = parse_ini_file($filename);
        $this->config->put('env', array_combine(array_map('strtolower', array_keys($env)), array_values($env)));
    }
}