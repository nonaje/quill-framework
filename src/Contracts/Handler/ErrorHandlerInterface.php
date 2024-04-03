<?php

declare(strict_types=1);

namespace Quill\Contracts\Handler;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface ErrorHandlerInterface
{
    public function capture(Throwable $e): ResponseInterface;
}