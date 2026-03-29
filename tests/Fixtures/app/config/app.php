<?php

declare(strict_types=1);

return [
    'name' => 'Fixture App',
    'middlewares' => [],
    'lifecycle' => [
        \Quill\Middleware\ExceptionHandlingMiddleware::class,
        \Quill\Middleware\RouteFinderMiddleware::class,
        \Quill\Middleware\ExecuteGlobalUserDefinedMiddlewares::class,
        \Quill\Middleware\ExecuteRouteMiddlewares::class,
    ],
];
