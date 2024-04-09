<?php

declare(strict_types=1);

namespace Quill\Contracts\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Quill\Enums\Http\HttpCode;

interface ResponseInterface
{
    public function getPsrResponse(): PsrResponseInterface;

    public function code(HttpCode $code): ResponseInterface;

    public function json(array $data): ResponseInterface;
}