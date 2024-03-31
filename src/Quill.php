<?php

declare(strict_types=1);

namespace Quill;

use Dotenv\Dotenv;
use Quill\Router\Router;
use Quill\Support\Helpers\Path;

final class Quill extends Router
{
    public function init(): self
    {
        require_once Path::quillFile('Support/Helpers/GlobalFunctions.php');

        $this->loadDotEnv();

        return $this;
    }

    public function loadApplicationRoutes(): self
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

        return $this;
    }

    public function loadDotEnv(): self
    {
        // Load .env into configuration items
        $env = parse_ini_file(Path::applicationFile('.env'));
        config()->put('env', array_combine(array_map('strtolower', array_keys($env)), array_values($env)));

        return $this;
    }
}