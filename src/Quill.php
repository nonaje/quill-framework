<?php

declare(strict_types=1);

namespace Quill;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Config\ConfigurationFilesLoader;
use Quill\Config\DotEnvLoader;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Quill\Contracts\Handler\RequestHandlerChainInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Factory\Middleware\MiddlewareFactory;
use Quill\Links\ExecuteRouteMiddlewares;
use Quill\Links\ExecuteRouteTarget;
use Quill\Links\HandlePossibleFutureError;
use Quill\Links\IdentifySearchedRoute;
use Quill\Links\ResolveRouteParameters;
use Quill\Router\Router;

final class Quill extends Router
{
    private array $configurationFiles;

    public function __construct(
        public readonly ConfigurationInterface          $config,
        private readonly RequestHandlerChainInterface   $chain,
        private ErrorHandlerInterface                   $errorHandler,
        RouteStoreInterface                             $store,
        MiddlewareStoreInterface                        $routerMiddlewares
    )
    {
        parent::__construct($store, $routerMiddlewares);

        // Last to run
        $this->chain->setLink(new ExecuteRouteTarget);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
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

    public function loadConfigurationFiles(string $filename, string ...$filenames): self
    {
        $loader = (new ConfigurationFilesLoader($this->config, func_get_args()));
        $loader();

        return $this;
    }

    public function loadDotEnv(string $filename = ''): self
    {
        $loader = (new DotEnvLoader($this->config, $filename));
        $loader();

        return $this;
    }
}
