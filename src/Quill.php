<?php

declare(strict_types=1);

namespace Quill;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Handler\RequestHandlerChainInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Factory\Middleware\MiddlewareFactory;
use Quill\Factory\Middleware\RequestHandlerFactory;
use Quill\Links\ExecuteRouteMiddlewares;
use Quill\Links\ExecuteRouteTarget;
use Quill\Links\HandlePossibleFutureError;
use Quill\Links\IdentifySearchedRoute;
use Quill\Links\ResolveRouteParameters;
use Quill\Router\Router;
use Quill\Support\Helpers\Path;
use Closure;

final class Quill extends Router
{
    public function __construct(
        public readonly ConfigurationInterface          $config,
        private readonly RequestHandlerChainInterface   $chain,
        private ErrorHandlerInterface                   $errorHandler,
        RouteStoreInterface                             $store,
        MiddlewareStoreInterface                        $routerMiddlewares
    )
    {
        parent::__construct($store, $routerMiddlewares);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Last to run
        $this->chain->setLink(new ExecuteRouteTarget);

        // Array reverse because the chain is LIFO
        // Position 0 of the array will be the first to be executed after being reverted.
        $this->using(array_reverse([
            new HandlePossibleFutureError($this->getErrorHandler()),
            new IdentifySearchedRoute($this->store),
            new ResolveRouteParameters,
            new ExecuteRouteMiddlewares
        ]));

        return $this->chain->getLink()->handle($request);
    }

    public function using(string|array|Closure|MiddlewareInterface $middleware): self
    {
        $middlewares = is_array($middleware) ? array_flatten($middleware) : [$middleware];

        foreach ($middlewares as $m) {
            if ($m instanceof MiddlewareInterface) {
                $instance = $m;
            } else {
                $instance = MiddlewareFactory::createMiddleware($m);
            }

            $this->chain->enchain($instance);
        }

        return $this;
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
