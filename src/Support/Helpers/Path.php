<?php

declare(strict_types=1);

namespace Quill\Support\Helpers;

final class Path
{
    /*
    |--------------------------------------------------------------------------
    | QUILL
    |--------------------------------------------------------------------------
    */
    public static function quillPath(): string
    {
        return __DIR__ . '/../..';
    }

    public static function quillFile(string $filename): string
    {
        return self::quillPath() . self::normalizeFilename($filename);
    }

    /*
    |--------------------------------------------------------------------------
    | APPLICATION
    |--------------------------------------------------------------------------
    */
    public static function applicationPath(): string
    {
        return __DIR__ . '/../../../../../..';
    }

    public static function applicationFile(string $filename): string
    {
        return self::applicationPath() . self::normalizeFilename($filename);
    }

    public static function configFile(string $filename): string
    {
        return self::applicationFile('config/') . self::normalizeFilename($filename);
    }

    public static function routeFile(string $filename): string
    {
        return self::applicationFile('routes/') . self::normalizeFilename($filename);
    }

    private static function normalizeFilename(string $filename): string
    {
        if (!str_starts_with($filename, '/')) {
            $filename = '/' . $filename;
        }

        if (str_ends_with($filename, '/')) {
            $filename = substr($filename, 0, -1);
        }

        return $filename;
    }
}