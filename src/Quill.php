<?php

declare(strict_types=1);

namespace Quill;

use InvalidArgumentException;
use Quill\Contracts\RouterInterface;
use Quill\Support\Pattern\Singleton;

final class Quill extends Singleton
{
    public const QUILL_BASE_PATH = __DIR__;

    private string $routesPath;

    public function __construct(
        private readonly RouterInterface $router
    )
    {

    }

    public function routes(string $path): self
    {
        if (! file_exists($path)) {
            throw new InvalidArgumentException('Please provide a valid route file');
        }

        $this->routesPath = $path;

        return $this;
    }

    public function loadRoutes(): self
    {
        $this->routes(__DIR__ . '/../../../../routes/api.php');
        $this->router->load($this->routesPath);
        return $this;
    }

    public function dispatch(): void
    {
        $this->router->dispatch();
    }
}