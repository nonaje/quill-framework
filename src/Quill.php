<?php

declare(strict_types=1);

namespace Quill;

use Closure;
use Nyholm\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Quill\Container\Container;
use Quill\Contracts\ApplicationInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\ErrorHandler\ErrorHandlerInterface;
use Quill\Contracts\Loader\FilesLoader;
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
use Quill\Middleware\ExecuteRouteMiddlewares;
use Quill\Middleware\FindRouteMiddleware;
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

class Quill extends Router implements ApplicationInterface
{
    protected function __construct(
        protected(set) MiddlewarePipelineInterface $pipeline,
        protected(set) MiddlewareStoreInterface $middlewares,
        protected(set) RequestInterface $request,
        protected(set) ResponseSenderInterface $response,
        RouteStoreInterface $routes,
        ContainerInterface $container
    ) {
        parent::__construct($container, $routes);
    }

    /** @inheritDoc */
    public function up(): never
    {
        /** @var ResponseInterface $response */
        $response = $this->pipeline
            ->send($this->request->getPsrRequest())
            ->through([
                new FindRouteMiddleware($this),
                // Run user-defined global middlewares before the route middlewares.
                ...$this->middlewares->all(),
                new ExecuteRouteMiddlewares(new $this->pipeline),
            ])
            ->to(new RequestHandler())
            ->getResponse();

        $this->response->send(new Response($response));
    }

    public function isProduction(): bool
    {
        return config('env.is_production', false);
    }

    public static function make(ContainerInterface $container, string $appRoot = ''): Quill
    {
        if (! $container->has(self::class)) {
            $container->register(
                id: self::class,
                resolver: fn (ContainerInterface $containre) => new self(
                    pipeline: $container->get(\Quill\Contracts\Middleware\MiddlewarePipelineInterface::class),
                    middlewares: $container->get(\Quill\Contracts\Router\MiddlewareStoreInterface::class),
                    request: $container->get(\Quill\Contracts\Request\RequestInterface::class),
                    response: $container->get(\Quill\Contracts\Response\ResponseSenderInterface::class),
                    routes: $container->get(\Quill\Contracts\Router\RouteStoreInterface::class),
                    container: $container
                )
            );

            $container->register(
                id: RouterInterface::class,
                resolver: fn (ContainerInterface $containre) => new Router($container, $container->get(RouteStoreInterface::class))
            );
        }

        return $container->get(self::class);
    }
}
