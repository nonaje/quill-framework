<?php

declare(strict_types=1);

namespace Quill\Factory;

use InvalidArgumentException;
use Quill\Configuration\Config;
use Quill\Container\Container;
use Quill\Contracts\ApplicationInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\ErrorHandler\ErrorHandlerInterface;
use Quill\Contracts\Middleware\MiddlewareFactoryInterface;
use Quill\Contracts\Middleware\MiddlewarePipelineInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Response\ResponseSenderInterface;
use Quill\Contracts\Router\MiddlewareStoreInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Support\PathResolverInterface;
use Quill\Handler\MiddlewarePipelineHandler;
use Quill\Handler\Error\JsonErrorHandler;
use Quill\Handler\RequestHandler;
use Quill\Middleware\ExceptionHandlingMiddleware;
use Quill\Loaders\ConfigurationFilesLoader;
use Quill\Loaders\DotEnvLoader;
use Quill\Loaders\RouteFilesLoader;
use Quill\Middleware\ExecuteGlobalUserDefinedMiddlewares;
use Quill\Middleware\ExecuteRouteMiddlewares;
use Quill\Middleware\RouteFinderMiddleware;
use Quill\Response\Response;
use Quill\Response\ResponseSender;
use Quill\Router\MiddlewareFactory;
use Quill\Router\MiddlewareStore;
use Quill\Router\Router;
use Quill\Support\Path;
use Quill\Quill;

final class QuillFactory
{
    private static ?string $defaultRoot = null;

    /** @var array<string, Quill> */
    private static array $sharedApplications = [];

    /** @var array<string, ContainerInterface> */
    private static array $sharedContainers = [];

    public static function useDefaultRoot(string $appRoot): void
    {
        self::$defaultRoot = self::normalizeRoot($appRoot);
    }

    public static function forget(?string $appRoot = null): void
    {
        if ($appRoot === null) {
            self::$sharedApplications = [];
            self::$sharedContainers = [];
            return;
        }

        $root = self::normalizeRoot($appRoot);
        unset(self::$sharedApplications[$root], self::$sharedContainers[$root]);
    }

    public static function shared(?string $appRoot = null, array $options = []): Quill
    {
        $root = self::normalizeRoot($appRoot ?? self::$defaultRoot ?? throw new InvalidArgumentException('Application root must be provided.'));

        if (!isset(self::$sharedApplications[$root])) {
            $context = self::bootstrap($root, $options);
            self::$sharedApplications[$root] = $context['app'];
            self::$sharedContainers[$root] = $context['container'];
        }

        return self::$sharedApplications[$root];
    }

    public static function make(string $appRoot, array $options = []): Quill
    {
        return self::bootstrap(self::normalizeRoot($appRoot), $options)['app'];
    }

    public static function container(?string $appRoot = null): ContainerInterface
    {
        $root = self::normalizeRoot($appRoot ?? self::$defaultRoot ?? throw new InvalidArgumentException('Application root must be provided.'));

        if (!isset(self::$sharedContainers[$root])) {
            self::shared($root);
        }

        return self::$sharedContainers[$root];
    }

    /**
     * @return array{app: Quill, container: ContainerInterface}
     */
    private static function bootstrap(string $root, array $options): array
    {
        $container = new Container();

        self::registerBaseBindings($container, $root, $options['config']['defaults'] ?? []);
        self::registerRuntimeBindings($container);
        self::applyServiceOverrides($container, $options);

        $app = Quill::make($container, $root);
        $container->refresh(Quill::class, static fn () => $app);
        $container->singleton(ApplicationInterface::class, static fn () => $app);
        $container->singleton(RouterInterface::class, static fn () => $app);

        self::bootstrapConfiguration($container, $options['config'] ?? []);
        self::bootstrapRoutes($container, $options['routes'] ?? []);

        if (isset($options['boot']) && is_callable($options['boot'])) {
            $options['boot']($app, $container);
        }

        return ['app' => $app, 'container' => $container];
    }

    private static function applyServiceOverrides(ContainerInterface $container, array $options): void
    {
        foreach ($options['bindings'] ?? [] as $id => $resolver) {
            if (is_string($id) && is_callable($resolver)) {
                $container->register($id, $resolver);
            }
        }

        foreach ($options['singletons'] ?? [] as $id => $resolver) {
            if (!is_string($id) || !is_callable($resolver)) {
                continue;
            }

            if ($container->has($id)) {
                $container->refresh($id, $resolver);
                continue;
            }

            $container->singleton($id, $resolver);
        }
    }

    private static function registerBaseBindings(Container $container, string $appRoot, array $defaults): void
    {
        $defaults = array_replace_recursive([
            'app' => [
                'middlewares' => [],
                'lifecycle' => [
                    ExceptionHandlingMiddleware::class,
                    RouteFinderMiddleware::class,
                    ExecuteGlobalUserDefinedMiddlewares::class,
                    ExecuteRouteMiddlewares::class,
                ],
            ],
        ], $defaults);

        $container->singleton(ContainerInterface::class, static fn () => $container);
        $container->singleton(PathResolverInterface::class, static fn () => new Path($appRoot));
        $container->singleton(ConfigurationInterface::class, static fn () => new Config($defaults));
        $container->singleton(ResponseInterface::class, static fn () => new Response());
    }

    private static function registerRuntimeBindings(Container $container): void
    {
        $container->singleton(ResponseSenderInterface::class, static fn () => new ResponseSender());
        $container->singleton(MiddlewarePipelineInterface::class, static fn () => new MiddlewarePipelineHandler());
        $container->singleton(MiddlewareFactoryInterface::class, static fn (ContainerInterface $container) => new MiddlewareFactory($container));
        $container->singleton(MiddlewareStoreInterface::class, static fn (ContainerInterface $container) => new MiddlewareStore($container));
        $container->singleton(Router::class, static fn (ContainerInterface $container) => new Router($container->get(MiddlewareFactoryInterface::class)));
        $container->singleton(RequestHandler::class, static fn (ContainerInterface $container) => new RequestHandler($container));
        $container->singleton(ErrorHandlerInterface::class, static fn (ContainerInterface $container) => new JsonErrorHandler(
            $container->get(ResponseInterface::class),
            $container->get(ResponseSenderInterface::class)
        ));
        $container->singleton(ExceptionHandlingMiddleware::class, static fn (ContainerInterface $container) => new ExceptionHandlingMiddleware(
            $container->get(ErrorHandlerInterface::class)
        ));
        $container->singleton(ExecuteGlobalUserDefinedMiddlewares::class, static fn (ContainerInterface $container) => new ExecuteGlobalUserDefinedMiddlewares(
            $container->get(MiddlewarePipelineInterface::class),
            $container->get(MiddlewareStoreInterface::class)
        ));
        $container->singleton(ExecuteRouteMiddlewares::class, static fn (ContainerInterface $container) => new ExecuteRouteMiddlewares(
            $container->get(MiddlewarePipelineInterface::class)
        ));
        $container->singleton(RouteFinderMiddleware::class, static fn (ContainerInterface $container) => new RouteFinderMiddleware(
            $container->get(RouterInterface::class)
        ));
    }

    private static function bootstrapConfiguration(ContainerInterface $container, array $options): void
    {
        /** @var ConfigurationInterface $config */
        $config = $container->get(ConfigurationInterface::class);

        $paths = $options['paths'] ?? [];
        (new ConfigurationFilesLoader($container))->load(...$paths);

        if (($options['load_dotenv'] ?? true) !== false) {
            $loader = new DotEnvLoader($container);
            $envFile = $options['env_file'] ?? null;

            if (is_string($envFile) && $envFile !== '') {
                $loader->load($envFile);
            } else {
                $loader->load();
            }
        }

        if (!empty($options['overrides'])) {
            $config->merge($options['overrides']);
        }
    }

    private static function bootstrapRoutes(ContainerInterface $container, array $options): void
    {
        if (($options['auto'] ?? true) === false) {
            return;
        }

        $paths = $options['paths'] ?? [];
        (new RouteFilesLoader($container))->load(...$paths);
    }

    private static function normalizeRoot(string $appRoot): string
    {
        $path = trim($appRoot);

        if ($path === '') {
            throw new InvalidArgumentException('Application root must be provided.');
        }

        $resolved = realpath($path);

        if ($resolved === false) {
            throw new InvalidArgumentException(sprintf('Application root "%s" does not exist.', $path));
        }

        return rtrim(str_replace('\\', '/', $resolved), '/');
    }
}
