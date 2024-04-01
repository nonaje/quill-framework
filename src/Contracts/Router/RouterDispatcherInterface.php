<?php

declare(strict_types=1);

namespace Quill\Contracts\Router;

interface RouterDispatcherInterface
{
    public function dispatch(): void;
}