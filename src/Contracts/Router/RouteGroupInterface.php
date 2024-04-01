<?php

namespace Quill\Contracts\Router;

interface RouteGroupInterface
{
    /** @return RouteInterface[] */
    public function resolveRoutes(): array;
}