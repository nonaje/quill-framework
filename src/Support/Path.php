<?php

declare(strict_types=1);

namespace Quill\Support;

use Exception;
use Quill\Contracts\Support\PathResolverInterface;

final readonly class Path implements PathResolverInterface
{
    public function __construct(private string $appRoot = APP_ROOT) { }

    /**
     * Converts a path to a properly formatted file path relative to application root
     *
     * @param string $filename Path relative to application root
     * @return string Fully qualified path
     * @throws Exception If application path has not been set
     */
    public function toFile(string $filename = ''): string
    {
        if (empty($filename)) {
            return $this->appRoot;
        }

        return $this->appRoot . self::normalize($filename);
    }

    /**
     * Normalizes a path to ensure consistent formatting
     *
     * @param string $path Path to normalize
     * @return string Normalized path
     */
    protected static function normalize(string $path): string
    {
        // Convert backslashes to forward slashes for consistency
        $path = str_replace('\\', '/', $path);

        // Trim slashes and ensure path starts with a slash
        return '/' . trim($path, '/');
    }
}
