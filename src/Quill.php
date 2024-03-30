<?php

declare(strict_types=1);

namespace Quill;

use Dotenv\Dotenv;
use Quill\Router\Router;

final class Quill extends Router
{
    public const string QUILL_BASE_PATH = __DIR__;

    public function loadRoutes(): self
    {
        $this->load(self::QUILL_BASE_PATH . '/../../../../routes/api.php');
        return $this;
    }

    public function loadDotEnv(): self
    {
        Dotenv::createImmutable(__DIR__ . '/../')->load();

        return $this;
    }
}