<?php

declare(strict_types=1);

namespace Quill\Response;

use Quill\Support\Pattern\Singleton;

class Response extends Singleton
{
    public static function send(array $payload = [], int $status = 200, array $headers = []): never
    {
        $headers['Content-Type'] ??= 'application/json';

        foreach ($headers as $key => $value) {
            if (is_int($key)) {
                header($value);
            }

            if (is_string($key)) {
                header("$key: $value");
            }
        }

        http_response_code($status);

        echo json_encode($payload);
        die;
    }

    public static function sendRouteNotFound(): never
    {
        static::send([
            'success' => false,
            'message' => "Route not found",
        ], 404);
    }
}
