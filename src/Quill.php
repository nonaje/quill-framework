<?php

declare(strict_types=1);

namespace Quill;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Quill\Contracts\Handler\RequestHandlerChainInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Factory\Psr7\Psr7Factory;
use Quill\Factory\QuillResponseFactory;
use Quill\Links\ExecuteRouteMiddlewares;
use Quill\Links\ExecuteRouteTarget;
use Quill\Links\HandlePossibleFutureError;
use Quill\Links\IdentifySearchedRoute;
use Quill\Links\ResolveRouteParameters;
use Quill\Response\ResponseMessenger;
use Quill\Router\Router;

final class Quill extends Router
{
    public function __construct(
        public readonly ConfigurationInterface          $config,
        private readonly FilesLoader                    $configurationFilesLoader,
        private readonly FilesLoader                    $dotEnvLoader,
        private readonly RequestHandlerChainInterface   $chain,
        private readonly MiddlewareStoreInterface       $uses,
        private ErrorHandlerInterface                   $errorHandler,
        RouteStoreInterface                             $store,
        MiddlewareStoreInterface                        $routerMiddlewares,
        FilesLoader                                     $routeFilesLoader,
    )
    {
        parent::__construct($routeFilesLoader, $store, $routerMiddlewares);

        // First element of the middleware chain but last to run (LIFO)
        $this->chain->setLastLink(new ExecuteRouteTarget);

        // Prepare middleware stack
        $this->uses->add([
            new HandlePossibleFutureError($this->getErrorHandler()),
            new IdentifySearchedRoute($this->store),
            new ResolveRouteParameters,
        ]);
    }

    public function up(): void
    {
        $request = Psr7Factory::createPsr7ServerRequest();

        $this->oilChain();

        $response = $this->unchain($request);

        $response = QuillResponseFactory::createFromPsrResponse($response);

        (new ResponseMessenger)->send($response);
    }

    private function oilChain(): void
    {
        $chain = $this->uses->all();
        $chain[] = new ExecuteRouteMiddlewares;
        $chain = array_reverse($chain);

        foreach ($chain as $link) {
            $this->chain->enchain($link);
        }
    }

    private function unchain(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->chain->getLastLink();

        return $handler->handle($request);
    }

    public function using(string|array|Closure|MiddlewareInterface $middleware): self
    {
        $this->uses->add([$middleware]);

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

    public function loadConfigurationFiles(string ...$filenames): self
    {
        $this->configurationFilesLoader->loadFiles($filenames);

        return $this;
    }

    public function loadDotEnv(string $filename = ''): self
    {
        $this->dotEnvLoader->loadFiles([$filename]);

        return $this;
    }
}
