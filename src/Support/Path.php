<?php

declare(strict_types=1);

namespace Quill\Support;

use InvalidArgumentException;
use Quill\Contracts\Support\PathResolverInterface;

final readonly class Path implements PathResolverInterface
{
    private string $appRoot;

    public function __construct(string $appRoot)
    {
        $appRoot = trim($appRoot);

        if ($appRoot === '') {
            throw new InvalidArgumentException('Application root must be provided.');
        }

        $resolved = realpath($appRoot);

        if ($resolved === false || !is_dir($resolved)) {
            throw new InvalidArgumentException(sprintf('Application root "%s" is not a readable directory.', $appRoot));
        }

        $this->appRoot = rtrim(self::normalizeSeparators($resolved), '/');
    }

    /**
     * Converts a path to a properly formatted file path relative to application root
     */
    public function toFile(string $filename = ''): string
    {
        if ($filename === '') {
            return $this->appRoot;
        }

        return $this->appRoot . '/' . self::normalizeRelativePath($filename);
    }

    private static function normalizeSeparators(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        return preg_replace('#/+#', '/', $path) ?: $path;
    }

    private static function normalizeRelativePath(string $path): string
    {
        return trim(self::normalizeSeparators($path), '/');
    }
}
