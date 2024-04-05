<?php

declare(strict_types=1);

namespace Quill\Factory;

use Quill\Config\Config;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Quill\Handler\RequestHandlerStack;
use Quill\Loaders\ConfigurationFilesLoader;
use Quill\Loaders\DotEnvLoader;
use Quill\Loaders\RouteFilesLoader;
use Quill\Quill;
use Quill\Response\ResponseMessenger;
use Quill\Router\MiddlewareStore;
use Quill\Router\Router;
use Quill\Router\RouteStore;
use Quill\Support\Dot\Parser;
use Quill\Support\PathFinder\Path;
use Throwable;

final class QuillFactory
{
    public static function make(): Quill
    {
        $errorHandler = new class implements ErrorHandlerInterface {
            public function captureException(Throwable $e): ResponseInterface
            {
                dump('EXCEPTION');
                dd($e->getMessage());
            }

            public function captureError(
                int $errorCode,
                string $errorDescription,
                string $filename = null,
                int $line = null,
                array $context = null
            ): ResponseInterface
            {
                dump('ERROR');
                dd(compact('errorCode', 'errorDescription', 'filename', 'line', 'context'));
            }
        };

        $config = Config::make(new Parser);

        return Quill::make(
            config: $config,
            configurationFilesLoader: new ConfigurationFilesLoader($config),
            dotEnvLoader: new DotEnvLoader($config),
            pathFinder: new Path,
            stack: new RequestHandlerStack,
            uses: new MiddlewareStore,
            messenger: new ResponseMessenger,
            errorHandler: $errorHandler,
            router: new Router(
                routeFilesLoader: new RouteFilesLoader,
                store: new RouteStore,
                middlewares: new MiddlewareStore,
            )
        );
    }
}