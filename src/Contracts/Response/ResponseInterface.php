<?php

declare(strict_types=1);

namespace Quill\Contracts\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Quill\Enum\Http\HttpCode;

interface ResponseInterface
{
    public function getPsrResponse(): PsrResponseInterface;

    public function setPsrResponse(PsrResponseInterface $response): self;

    public function code(HttpCode $code): self;

    public function json(array $data): self;
}