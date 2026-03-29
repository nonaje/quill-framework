<?php

declare(strict_types=1);

namespace Quill\Contracts;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Router\RouterInterface;

interface ApplicationInterface extends RouterInterface, RequestHandlerInterface
{
    public function container(): ContainerInterface;

    public function router(): RouterInterface;

    public function isProduction(): bool;

    public function use(string|array|Closure|MiddlewareInterface $middleware): static;

    public function processRequest(ServerRequestInterface $request): void;
}
