<?php

declare(strict_types=1);

namespace Quill\Support\Helpers;

final class Helpers
{
    public static function projectPath(): string
    {
        return __DIR__ . '/../../../../../..';
    }
}