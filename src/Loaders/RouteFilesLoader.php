<?php

declare(strict_types=1);

namespace Quill\Loaders;

use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Support\PathResolverInterface;
use Quill\Support\Path;

/**
 * Loads route definitions from files into the router
 */
final readonly class RouteFilesLoader implements FilesLoader
{
    /**
     * @param ContainerInterface $container The dependency injection container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Load route files from the routes directory
     *
     * @param string ...$filenames Optional specific route files to load
     * @return void
     */
    public function load(string ...$filenames): void
    {
        // If specific filenames are provided, load only those
        if (!empty($filenames)) {
            foreach ($filenames as $filename) {
                $this->loadRouteFile($filename);
            }
            return;
        }

        // Otherwise, load all routes from the routes directory
        $routesPath = $this->container->get(PathResolverInterface::class)->toFile('routes');

        if (!is_dir($routesPath)) {
            return;
        }

        $this->loadRoutesFromDirectory($routesPath);
    }

    /**
     * Load all route files from a directory
     *
     * @param string $directory The directory containing route files
     * @return void
     */
    private function loadRoutesFromDirectory(string $directory): void
    {
        $files = glob("$directory/*.php") ?: [];

        foreach ($files as $file) {
            $this->loadRouteFile($file);
        }
    }

    /**
     * Load a single route file
     *
     * @param string $file Path to the route file
     * @return void
     */
    private function loadRouteFile(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        $routes = require $file;

        if (is_callable($routes)) {
            $routes($this->container->get(RouterInterface::class));
        }
    }
}
