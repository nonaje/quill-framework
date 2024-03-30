<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return config("env.$key", $default);
    }
}

if (!function_exists('config')) {
    /** @return \Quill\Config\Config|mixed */
    function config(string $key = null, mixed $default = null)
    {
        $config = \Quill\Config\Config::make(
            new \Quill\Support\Dot\Parser()
        );

        if ($key === null) {
            return $config;
        }

        return $config->get($key, $default);
    }
}
