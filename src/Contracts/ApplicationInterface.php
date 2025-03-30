<?php

declare(strict_types=1);

namespace Quill\Contracts;

use Psr\Http\Message\ServerRequestInterface;

interface ApplicationInterface
{
    public function process(ServerRequestInterface $request): never;
}
