<?php

declare(strict_types=1);

if (!function_exists('resolve')) {
    function resolve(string $id): mixed
    {
        return \Quill\Container\Container::make()->get($id);
    }
}

if (!function_exists('refresh')) {
    function refresh(string $id, callable $refreshed): mixed
    {
        return \Quill\Container\Container::make()->refresh($id, $refreshed);
    }
}

if (!function_exists('app')) {
    function app(string $appRoot = ''): \Quill\Contracts\ApplicationInterface|\Quill\Contracts\Router\RouterInterface
    {
        return \Quill\Quill::make(\Quill\Container\Container::make(), $appRoot);
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

if (! function_exists('routes')) {
    function routes(string $path): mixed
    {
        return app()->get(\Quill\Contracts\Support\PathResolverInterface::class)->toFile("routes/$path");
    }
}

if (! function_exists('file_path')) {
    function file_path(string $path): mixed
    {
        return app()->get(\Quill\Contracts\Support\PathResolverInterface::class)->toFile("$path");
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
