<?php

declare(strict_types=1);

namespace Quill;

use Closure;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Container\Container;
use Quill\Contracts\ApplicationInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\ErrorHandler\ErrorHandlerInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Contracts\Middleware\MiddlewareFactoryInterface;
use Quill\Contracts\Middleware\MiddlewarePipelineInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseSenderInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouteGroupInterface;
use Quill\Contracts\Router\RouteInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Router\RouteStoreInterface;
use Quill\Contracts\Support\PathResolverInterface;
use Quill\Enums\Http\HttpMethod;
use Quill\Handler\Error\JsonErrorHandler;
use Quill\Handler\RequestHandler;
use Quill\Middleware\ExecuteGlobalUserDefinedMiddlewares;
use Quill\Middleware\ExecuteRouteMiddlewares;
use Quill\Middleware\RouteFinderMiddleware;
use Quill\Response\Response;
use Quill\Response\ResponseSender;
use Quill\Router\MiddlewareStore;
use Quill\Router\Route;
use Quill\Router\RouteGroup;
use Quill\Router\Router;
use Quill\Router\RouteStore;
use Quill\Support\Path;
use Throwable;
use Quill\Loaders\ConfigurationFilesLoader;
use Quill\Loaders\DotEnvLoader;
use Quill\Loaders\RouteFilesLoader;

class Quill extends Router implements ApplicationInterface, RequestHandlerInterface
{
    public protected(set) bool $isProduction;

    protected function __construct(
        protected MiddlewarePipelineInterface $pipeline,
        protected RequestHandlerInterface $requestHandler,
        protected ResponseSenderInterface $response,
        protected MiddlewareFactoryInterface $middlewareFactory,
        public protected(set) ContainerInterface $container
    ) {
        $this->isProduction = in_array(
            strtolower($this->container->get(ConfigurationInterface::class)->get('env.application_environment', 'development')),
            ['production', 'prod']
        );
    }

    public static function make(ContainerInterface $container, string $appRoot = ''): Quill
    {
        if (! $container->has(self::class)) {
            $container->register(
                id: self::class,
                resolver: fn (ContainerInterface $containre) => new self(
                    pipeline: $container->get(\Quill\Contracts\Middleware\MiddlewarePipelineInterface::class),
                    requestHandler: $container->get(\Quill\Handler\RequestHandler::class),
                    response: $container->get(\Quill\Contracts\Response\ResponseSenderInterface::class),
                    middlewareFactory: $container->get(MiddlewareFactoryInterface::class),
                    container: $container
                )
            );
        }

        return $container->get(self::class);
    }

    /** @inheritDoc */
    public function processRequest(ServerRequestInterface $request): never
    {
        $this->response->send($this->handle($request));
    }

    /** @ineritDoc */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->pipeline
            ->send($request)
            ->through($this->middlewares())
            ->to($this->requestHandler)
            ->getResponse();
    }

    public function use(string|array|\Closure|MiddlewareInterface $middleware): ApplicationInterface
    {
        /** @var ExecuteGlobalUserDefinedMiddlewares $pipeline */
        $pipeline = $this->container->get(ExecuteGlobalUserDefinedMiddlewares::class);
        $pipeline->middlewares->add($middleware);
    }

    protected function middlewares(): array
    {
        /** @var ConfigurationInterface $config */
        $config = $this->container->get(ConfigurationInterface::class);

        // Add global user defined middlewares
        $this->container->get(ExecuteGlobalUserDefinedMiddlewares::class)
            ->middlewares
            ->add($config->get('app.middlewares', []));

        return array_map(
            function (string $middleware) {
                return $this->container->get($middleware);
            },
            $config->get('app.lifecycle', [])
        );
    }
}
