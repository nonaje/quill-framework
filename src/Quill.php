<?php

declare(strict_types=1);

namespace Quill;

use Quill\Contracts\ConfigurationInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Request\Request;
use Quill\Response\Response;
use Quill\Router\Router;
use Quill\Router\RouterDispatcher;
use Quill\Router\RouteTargetCaller;
use Quill\Support\Helpers\Path;

final class Quill extends Router
{
    public function __construct(
        public readonly ConfigurationInterface $config,
        RouteStoreInterface                    $store,
        MiddlewareStoreInterface               $middlewares
    )
    {
        parent::__construct($store, $middlewares);
    }

    public function handle(): void
    {
        $dispatcher = new RouterDispatcher(
            request: Request::make(),
            response: Response::make(),
            store: $this->store,
            caller: new RouteTargetCaller
        );

        $dispatcher->dispatch();
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
            $this->config->put(
                key: substr(basename($filename), 0, -4),
                value: require_once $filename
            );
            return $this;
        }

        if (is_dir($filename)) {
            foreach (scandir($filename) as $filename) {
                $this->config->put(
                    key: substr(basename($filename), 0, -4),
                    value: require_once $filename
                );
            }
            return $this;
        }

        return $this;
    }
}