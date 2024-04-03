<?php

declare(strict_types=1);

namespace Quill\Contracts\Request;

use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface
{
    public function psrRequest(): ServerRequestInterface;
}