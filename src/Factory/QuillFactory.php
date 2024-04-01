<?php

namespace Quill\Factory;

use Quill\Config\Config;
use Quill\Quill;
use Quill\Request\Request;
use Quill\Response\Response;
use Quill\Router\RouterDispatcher;
use Quill\Router\RouteStore;
use Quill\Router\RouteTargetExecutor;
use Quill\Support\Dot\Parser;

final class QuillFactory
{
    public static function make(): Quill
    {
        return Quill::make(
            config: Config::make(
                new Parser()
            ),
            store: RouteStore::make(),
            dispatcher: new RouterDispatcher(
                request: Request::make(),
                response: Response::make(),
                config: Config::make(
                    new Parser()
                ),
                executor: new RouteTargetExecutor()
            )
        );
    }
}