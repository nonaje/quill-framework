<?php

declare(strict_types=1);

use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouterInterface;

return static function (RouterInterface $router): void {
    $router->get('/health', static fn ($request, ResponseInterface $response) => $response->json([
        'status' => 'ok',
    ]));
};
