<?php

declare(strict_types=1);

namespace Quill\Loaders;

use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Contracts\Support\PathResolverInterface;

final readonly class ConfigurationFilesLoader implements FilesLoader
{
    /**
     * PHP file extension constant
     */
    private const string PHP_EXTENSION = '.php';

    public function __construct(private ContainerInterface $container) { }

    /**
     * Load configuration files and store them in the configuration container
     *
     * @param string ...$filenames Paths to configuration files or directories
     */
    public function load(string ...$filenames): void
    {
        $configurationPath = $this->container->get(PathResolverInterface::class)->toFile('config');

        if (empty($filenames)) {
            $filenames = [$configurationPath];
        }

        foreach ($filenames as $filename) {
            if (is_file($filename)) {
                $this->loadFile($filename);
                continue;
            }

            if (is_dir($filename)) {
                $this->loadDirectory($filename);
            }
        }
    }

    /**
     * Load a single configuration file
     *
     * @param string $filepath Path to the configuration file
     * @return void
     */
    private function loadFile(string $filepath): void
    {
        // Extract filename without extension
        $key = pathinfo($filepath, PATHINFO_FILENAME);

        /** @var ConfigurationInterface $config */
        $config = $this->container->get(ConfigurationInterface::class);
        $config->put($key, require $filepath);
    }

    /**
     * Load all PHP files from a directory
     *
     * @param string $directory Path to configuration directory
     * @return void
     */
    private function loadDirectory(string $directory): void
    {
        $files = glob("$directory/*" . self::PHP_EXTENSION) ?: [];

        foreach ($files as $file) {
            $this->loadFile($file);
        }
    }
}
