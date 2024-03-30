<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default): mixed
    {
        return $_SERVER[$key] ?? $default;
    }
}