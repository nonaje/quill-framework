<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'global_functions.php';

define('APP_START', microtime(true));

use App\Core\Response\Response;
use Dotenv\Dotenv;
use App\Core\Router\Router;
use App\Core\Request\Request;
use App\Core\Config\Config;
use App\Core\Config\Parser as ConfigParser;

try {
    // TODO: Environment values are returning false, need review.
    // LOAD ENVIRONMENT VARIABLES VIA .env file
    Dotenv::createImmutable(__DIR__ . '/../')->load();

    // INITIALIZE APPLICATION ROUTER
    Router::make(
        request: Request::make($_SERVER),
        response: Response::make(),
        config: Config::make(new ConfigParser())
    )->load(__DIR__ . '/../src/routes/api.php')->dispatch();
} catch (\Throwable $e) {
    die('Error during application bootstrap: ' . PHP_EOL . $e->getMessage());
}
