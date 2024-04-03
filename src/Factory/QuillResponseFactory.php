<?php

namespace Quill\Factory;

use Quill\Contracts\Response\ResponseInterface;
use Quill\Factory\Psr7\Psr7Factory;
use Quill\Response\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class QuillResponseFactory extends Psr7Factory
{
    public static function createQuillResponse(): ResponseInterface
    {
        return Response::make(self::createPsr7Response());
    }

    private static function createPsr7Response(): PsrResponseInterface
    {
        return static::responseFactory()->createResponse();
    }
}