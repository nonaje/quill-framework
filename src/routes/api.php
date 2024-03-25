<?php

use App\Core\Config\Config;
use App\Core\Request\Request;
use App\Core\Response\Response;
use App\Core\Router\Router;
use App\Http\Controllers\HealthcheckController;
use App\Http\Middlewares\ExampleMiddleware;

$router = Router::make();

$router->get('/', function (Request $request, Response $response) {
    $response->send([
        'success' => true,
        'appName' => Config::make()->get('app.name'),
    ]);
});

$router
    ->get('healthcheck', [HealthcheckController::class])
    ->middleware(['example']);
