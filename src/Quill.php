<?php

declare(strict_types=1);

namespace Quill;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Quill\Contracts\ApplicationInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Handler\ErrorHandlerInterface;
use Quill\Contracts\Handler\RequestHandlerChainInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Contracts\Path\PathFinderInterface;
use Quill\Contracts\Response\ResponseMessengerInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Factory\Psr7\Psr7Factory;
use Quill\Factory\QuillResponseFactory;
use Quill\Links\ExecuteRouteMiddlewares;
use Quill\Links\ExecuteRouteTarget;
use Quill\Links\HandlePossibleFutureError;
use Quill\Links\IdentifySearchedRoute;
use Quill\Links\ResolveRouteParameters;
use Quill\Support\Pattern\Singleton;

final class Quill extends Singleton implements ApplicationInterface
{
    private ErrorHandlerInterface $errorHandler;

    protected function __construct(
        private readonly ConfigurationInterface         $config,
        private readonly FilesLoader                    $configurationFilesLoader,
        private readonly FilesLoader                    $dotEnvLoader,
        private readonly PathFinderInterface            $pathFinder,
        private readonly RequestHandlerChainInterface   $stack,
        private readonly MiddlewareStoreInterface       $uses,
        private readonly ResponseMessengerInterface     $messenger,
        private readonly RouterInterface                $router,
        ErrorHandlerInterface                           $errorHandler
    )
    {
        parent::__construct();

        $this->setErrorHandler($errorHandler);
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): ApplicationInterface
    {
        $this->errorHandler = $errorHandler;

        set_error_handler([$this->errorHandler, 'captureError']);
        set_exception_handler([$this->errorHandler, 'captureException']);

        return $this;
    }

    public function config(): ConfigurationInterface
    {
        return $this->config;
    }

    public function path(): PathFinderInterface
    {
        return $this->pathFinder;
    }

    public function up(): void
    {
        $request = Psr7Factory::createPsr7ServerRequest();

        $this->prepareLifecycle();

        $response = $this->handle($request);

        $response = QuillResponseFactory::createFromPsrResponse($response);

        $this->messenger->send($response);
    }

    public function router(): RouterInterface
    {
        return $this->router;
    }

    public function using(string|array|Closure|MiddlewareInterface $middleware): ApplicationInterface
    {
        $this->uses->add([$middleware]);

        return $this;
    }

    public function loadConfigurationFiles(string ...$filenames): ApplicationInterface
    {
        $this->configurationFilesLoader->loadFiles($filenames);

        return $this;
    }

    public function loadDotEnv(string $filename = ''): ApplicationInterface
    {
        $this->dotEnvLoader->loadFiles([$filename]);

        return $this;
    }

    private function prepareLifecycle(): void
    {
        // Quill middlewares (Required Lifecycle)
        $lifecycle = [
            new HandlePossibleFutureError($this->errorHandler),
            new IdentifySearchedRoute($this->router()),
            new ResolveRouteParameters,
            3 => new ExecuteRouteMiddlewares
        ];

        /*
            Sets the order of the user-defined global middlewares using the "using" function
            just before the route middlewares.
        */
        array_splice($lifecycle, 3, 0, $this->uses->all());

        // Transform multidimensional arrays into one-dimensional array
        $stack = array_flatten($lifecycle);

        // It is necessary to reverse the array to comply with the LIFO concept
        $stack = array_reverse($stack);

        // First element to be set but last to run (LIFO)
        $this->stack->setLast(new ExecuteRouteTarget);

        // Consequent links in the chain, the last element added is the first to be executed.
        foreach ($stack as $link) {
            $this->stack->stack($link);
        }
    }

    private function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get the las element added to the stack (ErrorHandler)
        $handler = $this->stack->getLast();

        // Run the error handler and start the stack execution chain
        return $handler->handle($request);
    }
}
