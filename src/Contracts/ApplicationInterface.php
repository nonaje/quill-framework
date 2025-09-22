<?php

declare(strict_types=1);

namespace Quill\Contracts;

use Psr\Http\Message\ServerRequestInterface;
use Quill\Contracts\Container\ContainerInterface;

interface ApplicationInterface
{
    protected(set) ContainerInterface $container { get; set; }

    protected(set) bool $isProduction { get; set; }

    public function processRequest(ServerRequestInterface $request): never;
}
