<?php

declare(strict_types=1);

namespace Quill;

use Closure;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\ApplicationInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\ErrorHandler\ErrorHandlerInterface;
use Quill\Contracts\Middleware\MiddlewarePipelineInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Response\ResponseSenderInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Handler\Error\ErrorHandler as BaseErrorHandler;
use Quill\Handler\RequestHandler;
use Quill\Middleware\ExceptionHandlingMiddleware;
use Quill\Middleware\ExecuteGlobalUserDefinedMiddlewares;
use Quill\Middleware\ExecuteRouteMiddlewares;
use Quill\Middleware\RouteFinderMiddleware;
use Quill\Request\Request;
use Quill\Response\Response;
use Quill\Router\Router;

final class Quill implements ApplicationInterface
{
    private function __construct(
        private readonly RouterInterface $router,
        private readonly MiddlewarePipelineInterface $pipeline,
        private readonly RequestHandlerInterface $requestHandler,
        private readonly ResponseSenderInterface $responseSender,
        private readonly ContainerInterface $container,
    ) {
    }

    private ?ErrorHandlerInterface $listenedErrorHandler = null;

    public static function make(ContainerInterface $container, string $appRoot = ''): self
    {
        if (! $container->has(self::class)) {
            $container->singleton(
                self::class,
                static fn (ContainerInterface $container): self => new self(
                    router: $container->has(Router::class)
                        ? $container->get(Router::class)
                        : new Router($container->get(\Quill\Contracts\Middleware\MiddlewareFactoryInterface::class)),
                    pipeline: $container->get(MiddlewarePipelineInterface::class),
                    requestHandler: $container->get(RequestHandler::class),
                    responseSender: $container->get(ResponseSenderInterface::class),
                    container: $container,
                )
            );
        }

        return $container->get(self::class);
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }

    public function router(): RouterInterface
    {
        return $this->router;
    }

    public function isProduction(): bool
    {
        /** @var ConfigurationInterface $config */
        $config = $this->container->get(ConfigurationInterface::class);

        $environment = strtolower((string) (
            $config->get('env.app_env')
            ?? $config->get('env.application_environment')
            ?? 'development'
        ));

        return in_array($environment, ['production', 'prod'], true);
    }

    public function processRequest(ServerRequestInterface $request): void
    {
        $this->responseSender->send($this->handle($request));
    }

    public function handle(ServerRequestInterface $request): PsrResponseInterface
    {
        $this->preparePerRequestBindings($request);
        $this->configureErrorHandling();

        return $this->pipeline
            ->send($request)
            ->through($this->lifecycleMiddlewares())
            ->to($this->requestHandler)
            ->getResponse();
    }

    public function use(string|array|Closure|MiddlewareInterface $middleware): static
    {
        $this->globalUserDefinedMiddlewares()->middlewareStore()->add($middleware);

        return $this;
    }

    public function group(string $prefix, Closure $routes, array $middlewares = []): void
    {
        $this->router->group($prefix, $routes, $middlewares);
    }

    public function get(string $path, Closure|array|string $target, array $middlewares = []): void
    {
        $this->router->get($path, $target, $middlewares);
    }

    public function post(string $path, Closure|array|string $target, array $middlewares = []): void
    {
        $this->router->post($path, $target, $middlewares);
    }

    public function put(string $path, Closure|array|string $target, array $middlewares = []): void
    {
        $this->router->put($path, $target, $middlewares);
    }

    public function patch(string $path, Closure|array|string $target, array $middlewares = []): void
    {
        $this->router->patch($path, $target, $middlewares);
    }

    public function delete(string $path, Closure|array|string $target, array $middlewares = []): void
    {
        $this->router->delete($path, $target, $middlewares);
    }

    public function head(string $path, Closure|array|string $target, array $middlewares = []): void
    {
        $this->router->head($path, $target, $middlewares);
    }

    public function options(string $path, Closure|array|string $target, array $middlewares = []): void
    {
        $this->router->options($path, $target, $middlewares);
    }

    public function routes(): array
    {
        return $this->router->routes();
    }

    public function clear(): void
    {
        $this->router->clear();
    }

    private function preparePerRequestBindings(ServerRequestInterface $request): void
    {
        if ($this->container->has(RequestInterface::class)) {
            $this->container->refresh(RequestInterface::class, static fn (): RequestInterface => new Request($request));
        } else {
            $this->container->singleton(RequestInterface::class, static fn (): RequestInterface => new Request($request));
        }

        $this->container->refresh(ResponseInterface::class, static fn (): ResponseInterface => new Response());
    }

    /**
     * @return list<MiddlewareInterface>
     */
    private function lifecycleMiddlewares(): array
    {
        /** @var ConfigurationInterface $config */
        $config = $this->container->get(ConfigurationInterface::class);

        $this->globalUserDefinedMiddlewares()
            ->middlewareStore()
            ->reset()
            ->add($config->get('app.middlewares', []));

        return array_map(
            fn (string $middleware): MiddlewareInterface => $this->container->get($middleware),
            $config->get('app.lifecycle', [
                ExceptionHandlingMiddleware::class,
                RouteFinderMiddleware::class,
                ExecuteGlobalUserDefinedMiddlewares::class,
                ExecuteRouteMiddlewares::class,
            ])
        );
    }

    private function globalUserDefinedMiddlewares(): ExecuteGlobalUserDefinedMiddlewares
    {
        return $this->container->get(ExecuteGlobalUserDefinedMiddlewares::class);
    }

    private function configureErrorHandling(): void
    {
        if (! $this->container->has(ErrorHandlerInterface::class)) {
            return;
        }

        $errorHandler = $this->container->get(ErrorHandlerInterface::class);

        if ($this->listenedErrorHandler !== $errorHandler) {
            $errorHandler->listen();
            $this->listenedErrorHandler = $errorHandler;
        }

        if ($errorHandler instanceof BaseErrorHandler) {
            $isProduction = $this->isProduction();
            $errorHandler->displayErrors = ! $isProduction;
            $errorHandler->logErrors = $isProduction;
        }
    }
}
