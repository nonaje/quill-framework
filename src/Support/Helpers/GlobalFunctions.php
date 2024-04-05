<?php

declare(strict_types=1);

use Quill\Config\Config;
use Quill\Contracts\ApplicationInterface;
use Quill\Contracts\Path\PathFinderInterface;
use Quill\Factory\QuillFactory;
use Quill\Support\Dot\Parser;

if (!function_exists('quill')) {
    function quill(): ApplicationInterface
    {
        return QuillFactory::make();
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return config("env.$key", $default);
    }
}

if (!function_exists('config')) {
    /** @return Config|mixed */
    function config(string $key = null, mixed $default = null): mixed
    {
        $config = Config::make(new Parser());

        if ($key === null) {
            return $config;
        }

        return $config->get($key, $default);
    }
}

if (!function_exists('path')) {
    /**
     * @return PathFinderInterface
     */
    function path(): PathFinderInterface
    {
        return quill()->path();
    }
}

if (!function_exists('array_flatten')) {
    function array_flatten(array $toFlatten): array
    {
        $results = [];

        foreach ($toFlatten as $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, array_flatten($value));
            } else {
                $results[] = $value;
            }
        }

        return array_values($results);
    }
}
