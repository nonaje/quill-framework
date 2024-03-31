<?php

declare(strict_types=1);

namespace Quill;

use Dotenv\Dotenv;
use Quill\Router\Router;
use Quill\Support\Helpers\Path;

final class Quill extends Router
{
    public function init(): void
    {
        $this->loadGlobalFunctions();
        loadApplicationConfiguration;
    }

    public function loadApplicationRoutes(): void
    {
        $routesPath = Path::applicationFile('routes');

        if (file_exists($routesPath)) {
            foreach (scandir($routesPath) as $filename) {
                if (str_ends_with($filename, '.php')) {
                    $routes = Path::applicationFile("routes/$filename");
                    $this->load($routes);
                }
            }
        }
    }

    private function loadGlobalFunctions(): void
    {
        require_once Path::quillFile('Support/Helpers/GlobalFunctions.php');
    }

    private function loadApplicationConfiguration(): void
    {
        // Load .env into configuration items
        $env = parse_ini_file(Path::applicationFile('.env'));
        config()->put('env', array_combine(array_map('strtolower', array_keys($env)), array_values($env)));

        $files = array_filter(
            scandir(Path::applicationFile('config')),
            fn (string $filename) => str_ends_with($filename, '.php') && Path::configFile($filename)
        );

        foreach ($files as $filename) {
            config()->put(
                key: substr($filename, 0, -4),
                value: require_once Path::configFile($filename)
            );
        }
    }
}