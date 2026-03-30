<?php

declare(strict_types=1);

namespace Quill\Request;

use Psr\Http\Message\ServerRequestInterface;
use Quill\Contracts\Request\RequestFactoryInterface;
use Quill\Contracts\Request\RequestInterface;

final class RequestFactory implements RequestFactoryInterface
{
    public function make(ServerRequestInterface $request): RequestInterface
    {
        return new Request($request);
    }
}
