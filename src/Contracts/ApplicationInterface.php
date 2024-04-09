<?php

declare(strict_types=1);

namespace Quill\Contracts;

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Support\PathFinderInterface;

interface ApplicationInterface
{
    public function router(): RouterInterface;

    public function config(): ConfigurationInterface;

    public function path(): PathFinderInterface;

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): ApplicationInterface;

    public function loadConfigurationFiles(string ...$filenames): ApplicationInterface;

    public function loadDotEnv(string $filename = ''): ApplicationInterface;

    public function using(string|array|Closure|MiddlewareInterface $middleware): ApplicationInterface;

    public function up(): void;
}
