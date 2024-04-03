<?php

declare(strict_types=1);

namespace Quill\Contracts\Handler;

use Quill\Contracts\Response\ResponseInterface;
use Throwable;

interface ErrorHandlerInterface
{
    public function capture(Throwable $e): ResponseInterface;
}