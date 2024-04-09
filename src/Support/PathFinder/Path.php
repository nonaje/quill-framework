<?php

declare(strict_types=1);

namespace Quill\Support\PathFinder;

use Quill\Contracts\Support\PathFinderInterface;
use Quill\Exceptions\FileNotFoundException;
use Quill\Support\Traits\Singleton;

/**
 *
 */
final class Path implements PathFinderInterface
{
    use Singleton;

    protected function __construct(
        private string $applicationPath = __DIR__ . '/../../../../../..'
    )
    {
    }

    public function quillFile(string $filename): string
    {
        return self::quillPath() . self::normalizeFilename($filename);
    }

    public function quillPath(): string
    {
        return __DIR__ . '/../..';
    }

    private function normalizeFilename(string $filename): string
    {
        $filename = trim($filename, '/');

        return "/$filename";
    }

    public function setApplicationPath(string $path): void
    {
        if (! file_exists($path)) {
            throw new FileNotFoundException($path);
        }

        $this->applicationPath = $path;
    }

    public function configFile(string $filename): string
    {
        return self::applicationFile('config/') . self::normalizeFilename($filename);
    }

    public function applicationFile(string $filename): string
    {
        return $this->applicationPath . self::normalizeFilename($filename);
    }

    public function routeFile(string $filename): string
    {
        return self::applicationFile('routes/') . self::normalizeFilename($filename);
    }
}