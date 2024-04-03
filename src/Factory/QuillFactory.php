<?php

declare(strict_types=1);

namespace Quill\Factory;

use Quill\Config\Config;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Quill;
use Quill\Response\Response;
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
                dd($e);
            }
        };

        return new Quill(
            config: Config::make(new Parser),
            errorHandler: $errorHandler,
            store: new RouteStore,
            middlewares: new MiddlewareStore
        );
    }
}