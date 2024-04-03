<?php

declare(strict_types=1);

namespace Quill\Factory;

use Quill\Config\Config;
use Quill\Quill;
use Quill\Router\MiddlewareStore;
use Quill\Router\RouteStore;
use Quill\Support\Dot\Parser;

final class QuillFactory
{
    public static function make(): Quill
    {
        $errorHandler = new class implements \Quill\Contracts\Handler\ErrorHandlerInterface {
            public function capture(\Throwable $e): \Quill\Contracts\Response\ResponseInterface
            {
                \Quill\Response\Response::make()->send([
                    'success' => false,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]);
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