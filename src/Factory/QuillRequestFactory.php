<?php

declare(strict_types=1);

namespace Quill\Factory;

use Psr\Http\Message\ServerRequestInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Factory\Psr7\Psr7Factory;
use Quill\Request\Request;

class QuillRequestFactory extends Psr7Factory
{
    public static function createQuillRequest(): RequestInterface
    {
        return new Request(static::createPsr7ServerRequest());
    }

    public static function createFromPsrRequest(ServerRequestInterface $request): RequestInterface
    {
        return new Request($request);
    }
}