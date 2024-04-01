<?php

declare(strict_types=1);

namespace Quill\Factory;

use Quill\Config\Config;
use Quill\Quill;
use Quill\Request\Request;
use Quill\Response\Response;
use Quill\Router\RouterDispatcher;
use Quill\Router\RouteStore;
use Quill\Router\RouteTargetCaller;
use Quill\Support\Dot\Parser;

final class QuillFactory
{
    public static function make(): Quill
    {
        return new Quill(
            config: Config::make(new Parser),
            store: new RouteStore
        );
    }
}