<?php

declare(strict_types=1);

namespace Quill\Contracts;

use Quill\Request\Request;
use Quill\Response\Response;

interface MiddlewareInterface
{
    // TODO: Psr7 Implementation
    public function handle(Request $request, Response $response): void;
}