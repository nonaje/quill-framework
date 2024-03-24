<?php

use App\Core\Request\Request;
use App\Core\Response\Response;
use App\Core\Router\Router;

$router = Router::make();

$router->get('/', function (Request $request, Response $response) {
    $response->send([
        'success' => true,
        'message' => \App\Core\Config\Config::make()->get('app.name'),
    ]);
});

$router
    ->get('/api/healthcheck', [\App\Http\Controllers\HealthcheckController::class])
    ->middleware(['auth']);
