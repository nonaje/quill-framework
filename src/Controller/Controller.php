<?php

declare(strict_types=1);

namespace Quill\Controller;

use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;

abstract class Controller
{
    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly RequestInterface $request,
        protected readonly ResponseInterface $response
    ) {
    }
}
