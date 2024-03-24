<?php

return [
    'name' => env('APP_NAME', 'lightweight_api'),
    'environment' => env('APP_ENVIRONMENT', 'dev'),
    'router' => [
        /*
        |--------------------------------------------------------------------------
        | Available global middlewares
        |--------------------------------------------------------------------------
        |
        | Available middlewares: auth
        */
        'middlewares' => [
            'auth' => \App\Http\Middlewares\Authenticate::class,
        ],
    ],
];
