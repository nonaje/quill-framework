<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'global_functions.php';

define('APP_START', microtime(true));

use App\Core\Config\Config;
use App\Core\Config\Parser as ConfigParser;
use App\Core\Request\Request;
use App\Core\Response\Response;
use App\Core\Router\MiddlewareValidator;
use App\Core\Router\Router;
use App\Core\Router\RouterDispatcher;
use App\Core\Router\RouteStore;
use Dotenv\Dotenv;
use App\Core\Router\RouteTargetExecutor;

try {
    // LOAD ENVIRONMENT VARIABLES VIA .env file
    Dotenv::createImmutable(__DIR__ . '/../')->load();

    // INITIALIZE APPLICATION CONFIGURATION
    Config::make(new ConfigParser());

    // INITIALIZE APPLICATION ROUTER
    Router::make(
        store: RouteStore::make(),
        dispatcher: new RouterDispatcher(
            request: Request::make(),
            response: Response::make(),
            config: Config::make(),
            executor: new RouteTargetExecutor
        ),
        middleware: new MiddlewareValidator
    )->load(__DIR__ . '/../src/routes/api.php')->dispatch();

} catch (Throwable $e) {
    dd('Error during application bootstrap: ', $e);
}
