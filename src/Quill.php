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
        $this->loadDotEnv();
        $this->loadGlobalFunctions();
        $this->dispatch();
    }

    private function loadRoutes(): void
    {
        $this->load(Path::routeFile('api.php'));
    }

    private function loadDotEnv(): void
    {
        Dotenv::createImmutable(Path::applicationPath())->load();
    }

    private function loadGlobalFunctions(): void
    {
        require_once Path::quillFile('Support/Helpers/GlobalFunctions.php');
    }
}