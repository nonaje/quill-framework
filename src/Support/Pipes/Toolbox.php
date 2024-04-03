<?php

namespace Quill\Support\Pipes;

use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouteStoreInterface;

final readonly class Toolbox
{
    public function __construct(
        public RouteStoreInterface $store,
        public RequestInterface $request,
        public ResponseInterface $response
    )
    {
    }
}