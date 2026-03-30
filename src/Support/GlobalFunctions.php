<?php

declare(strict_types=1);

if (!function_exists('resolve')) {
    function resolve(string $id): mixed
    {
        return \Quill\Factory\QuillFactory::container()->get($id);
    }
}

if (!function_exists('refresh')) {
    function refresh(string $id, callable $refreshed): mixed
    {
        return \Quill\Factory\QuillFactory::container()->refresh($id, $refreshed);
    }
}

if (!function_exists('app')) {
    function app(string $appRoot = '', array $options = []): \Quill\Contracts\ApplicationInterface
    {
        if ($appRoot !== '') {
            \Quill\Factory\QuillFactory::useDefaultRoot($appRoot);

            return \Quill\Factory\QuillFactory::shared($appRoot, $options);
        }

        return \Quill\Factory\QuillFactory::shared(null, $options);
    }
}

if (!function_exists('request')) {
    function request(): \Quill\Contracts\Request\RequestInterface
    {
        return resolve(\Quill\Contracts\Request\RequestInterface::class);
    }
}

if (!function_exists('response')) {
    function response(): \Quill\Contracts\Response\ResponseInterface
    {
        return resolve(\Quill\Contracts\Response\ResponseInterface::class);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return config("env.$key", $default);
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = resolve(\Quill\Contracts\Configuration\ConfigurationInterface::class);
        return $key ? $config->get($key, $default) : $config;
    }
}

if (!function_exists('array_flatten')) {
    function array_flatten(array $toFlatten): array
    {
        $results = [];

        foreach ($toFlatten as $value) {
            if (is_array($value)) {
                $results = array_merge($results, array_flatten($value));
            } else {
                $results[] = $value;
            }
        }

        return array_values($results);
    }
}
