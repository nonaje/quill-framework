<?php

declare(strict_types=1);

namespace Quill\Factory;

use Quill\Config\Config;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Quill\Handler\RequestHandlerChain;
use Quill\Loaders\ConfigurationFilesLoader;
use Quill\Loaders\DotEnvLoader;
use Quill\Loaders\RouteFilesLoader;
use Quill\Quill;
use Quill\Router\MiddlewareStore;
use Quill\Router\RouteStore;
use Quill\Support\Dot\Parser;
use Throwable;

final class QuillFactory
{
    public static function make(): Quill
    {
        $errorHandler = new class implements ErrorHandlerInterface {
            public function capture(Throwable $e): ResponseInterface
            {
                dump('ERROR');
                dd($e->getMessage());
            }
        };

        $config = Config::make(new Parser);

        return new Quill(
            config: $config,
            configurationFilesLoader: new ConfigurationFilesLoader($config),
            dotEnvLoader: new DotEnvLoader($config),
            chain: new RequestHandlerChain,
            uses: new MiddlewareStore,
            errorHandler: $errorHandler,
            store: new RouteStore,
            routerMiddlewares: new MiddlewareStore,
            routeFilesLoader: new RouteFilesLoader
        );
    }
}