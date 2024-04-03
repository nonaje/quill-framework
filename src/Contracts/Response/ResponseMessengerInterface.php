<?php

declare(strict_types=1);

namespace Quill\Contracts\Response;

interface ResponseMessengerInterface
{
    public function send(ResponseInterface $response): void;
}