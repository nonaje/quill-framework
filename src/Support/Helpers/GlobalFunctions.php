<?php

declare(strict_types=1);

use Quill\Config\Config;
use Quill\Support\Dot\Parser;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return config("env.$key", $default);
    }
}

if (!function_exists('config')) {
    /** @return Config|mixed */
    function config(string $key = null, mixed $default = null)
    {
        $config = Config::make(
            new Parser()
        );

        if ($key === null) {
            return $config;
        }

        return $config->get($key, $default);
    }
}
