<?php

declare(strict_types=1);

namespace Quill\Contracts\Request;

use Psr\Http\Message\ServerRequestInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Router\Route;

interface RequestInterface
{
    public function psrRequest(): ServerRequestInterface;

    public function setMatchedRoute(RouteInterface $route): self;

    public function getMatchedRoute(): null|RouteInterface;
}