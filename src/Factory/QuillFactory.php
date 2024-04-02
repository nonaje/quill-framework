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
        return new Quill(
            config: Config::make(new Parser),
            store: new RouteStore,
            middlewares: new MiddlewareStore
        );
    }
}