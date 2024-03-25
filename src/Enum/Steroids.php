<?php

declare(strict_types=1);

namespace Quill\Enum;

trait Steroids
{
    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
