<?php

declare(strict_types=1);

namespace Quill;

use Quill\Config\Config;
use Quill\Router\Router;
use Quill\Router\RouterDispatcher;
use Quill\Router\RouteStore;
use Quill\Support\Helpers\Path;

final class Quill extends Router
{
    protected function __construct(
        public readonly Config $config,
        RouteStore             $store,
        RouterDispatcher       $dispatcher
    )
    {
        parent::__construct($store, $dispatcher);

    }

    public function loadDotEnv(string $filename = null): self
    {
        $filename ??= Path::applicationFile('.env');

        if (file_exists($filename)) {
            // Load .env into configuration items
            $env = parse_ini_file($filename);
            config()->put('env', array_combine(array_map('strtolower', array_keys($env)), array_values($env)));
        }

        return $this;
    }

    public function loadConfig(string $filename = null): self
    {
        $filename ??= Path::applicationFile('config');

        if (!file_exists($filename)) {
            return $this;
        }

        if (is_file($filename)) {
            $key = substr(basename($filename), 0, -4);
            $this->config->put($key, require_once $filename);
            return $this;
        }

        if (is_dir($filename)) {
            foreach (scandir($filename) as $filename) {
                $key = substr(basename($filename), 0, -4);
                $this->config->put($key, require_once $filename);
                return $this;
            }
        }

        return $this;
    }
}