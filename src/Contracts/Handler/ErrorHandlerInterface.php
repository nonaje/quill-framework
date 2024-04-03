<?php

namespace Quill\Contracts\Handler;

use Quill\Contracts\Response\ResponseInterface;

interface ErrorHandlerInterface
{
    public function capture(\Throwable $e): ResponseInterface;
}