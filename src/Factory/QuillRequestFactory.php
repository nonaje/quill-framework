<?php

declare(strict_types=1);

namespace Quill\Factory;

use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Factory\Psr7\Psr7Factory;
use Quill\Request\Request;

class QuillRequestFactory extends Psr7Factory
{
    public static function createQuillRequest(): RequestInterface
    {
        return Request::make(self::createPsr7ServerRequest());
    }

    private static function createPsr7ServerRequest(): ServerRequestInterface
    {
        return (new ServerRequestCreator(
            serverRequestFactory: static::serverRequestFactory(),
            uriFactory: static::uriFactory(),
            uploadedFileFactory: static::uploadedFileFactory(),
            streamFactory: static::streamFactory()
        ))->fromGlobals();
    }
}