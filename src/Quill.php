<?php

declare(strict_types=1);

namespace Quill;

use InvalidArgumentException;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Pipes\ExecuteRouteMiddlewares;
use Quill\Pipes\ExecuteRouteTarget;
use Quill\Pipes\HandlePossibleFutureError;
use Quill\Pipes\IdentifySearchedRoute;
use Quill\Pipes\ResolveRouteParameters;
use Quill\Router\Router;
use Quill\Support\Helpers\Path;
use Quill\Support\Pattern\Pipeline;

final class Quill extends Router
{
    public function __construct(
        public readonly ConfigurationInterface $config,
        private ErrorHandlerInterface          $errorHandler,
        RouteStoreInterface                    $store,
        MiddlewareStoreInterface               $middlewares
    )
    {
        parent::__construct($store, $middlewares);
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = (new Pipeline())
            ->send($request)
            ->using([
                new HandlePossibleFutureError($this->getErrorHandler()),
                new IdentifySearchedRoute($this->store),
                ResolveRouteParameters::class,
                ExecuteRouteMiddlewares::class,
                ExecuteRouteTarget::class
            ])
            ->exec();

        return $response;
    }

    public function getErrorHandler(): ErrorHandlerInterface
    {
        return $this->errorHandler;
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): self
    {
        $this->errorHandler = $errorHandler;

        return $this;
    }

    public function loadDotEnv(string $filename = null): self
    {
        $filename ??= Path::applicationFile('.env');

        if (!file_exists($filename)) {
            throw new InvalidArgumentException();
        }

        // Load .env into configuration items
        $env = parse_ini_file($filename);
        config()->put('env', array_combine(array_map('strtolower', array_keys($env)), array_values($env)));

        return $this;
    }

    public function loadConfig(string $filename = null): self
    {
        $filename ??= Path::applicationFile('config');

        if (!file_exists($filename)) {
            return $this;
        }

        if (is_file($filename)) {
            $this->config->put(
                key: substr(basename($filename), 0, -4),
                value: require_once $filename
            );
            return $this;
        }

        if (is_dir($filename)) {
            foreach (scandir($filename) as $filename) {
                $this->config->put(
                    key: substr(basename($filename), 0, -4),
                    value: require_once $filename
                );
            }
            return $this;
        }

        return $this;
    }
}