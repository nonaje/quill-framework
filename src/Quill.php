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
        $this->loadRoutes();
        $this->loadGlobalFunctions();
        $this->loadConfig();
        $this->dispatch();
    }

    private function loadRoutes(): void
    {
        $this->load(Path::routeFile('api.php'));
    }

    private function loadConfig(): void
    {
        $files = array_filter(
            scandir(Path::applicationFile('config')),
            fn (string $filename) => str_ends_with($filename, '.php') && Path::configFile($filename)
        );

        $files = array_map(
            fn (string $filename) => substr($filename, 0, -4),
            $files
        );

        $files[] = 'env';

        config()->load(array_values($files));
    }

    private function loadGlobalFunctions(): void
    {
        require_once Path::quillFile('Support/Helpers/GlobalFunctions.php');
    }
}