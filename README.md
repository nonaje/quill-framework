# Quill Framework

`quill-framework` is a reusable HTTP kernel: a container, router, middleware pipeline, request/response contracts, and supporting loaders that can be embedded inside any composition root. It does **not** ship a runtime executable or starter project—those responsibilities move to `quill-app`.

- Read `docs/framework-boundary.md` for the canonical split between the framework and consumers.
- Read `docs/quill-app-skeleton.md` for the minimal responsibilities planned for the official starter.

## Programmatic Bootstrap

Provide the framework with explicit filesystem inputs and service overrides, then let your own runtime feed PSR-7 requests into the resulting application:

```php
use Nyholm\Psr7Server\ServerRequestCreator;
use Quill\Contracts\Response\ResponseSenderInterface;
use Quill\Factory\QuillFactory;

require __DIR__ . '/vendor/autoload.php';

$root = __DIR__;

$app = QuillFactory::shared($root, [
    'config' => [
        'paths' => [$root . '/config'],
        'env_paths' => [$root . '/.env', $root . '/.env.local'],
        'defaults' => ['app' => ['debug' => false]],
        'overrides' => ['app' => ['debug' => getenv('APP_DEBUG') === 'true']],
    ],
    'routes' => [
        'paths' => [$root . '/routes'],
    ],
    'singletons' => [
        ResponseSenderInterface::class => static fn () => new CustomSender(),
    ],
]);

$app->get('/health', static fn ($request, $response) => $response->json(['ok' => true]));

$creator = ServerRequestCreator::fromGlobals();
$app->handle($creator->fromGlobals());
```

- `QuillFactory::make($root, $options)` returns a fresh container every call.
- `QuillFactory::shared($root, $options)` caches one instance per root; call `QuillFactory::forget($root)` (or without arguments) when workers/tests need a reset.
- `app($root, $options)` is a thin helper that records the default root and proxies to `QuillFactory::shared()`. Once the root is set you may call `app()` without arguments.
- `QuillFactory::container($root)` exposes the DI container if you need lower-level access.

Nothing is auto-loaded anymore: if you want `.env` files, configuration directories, or route trees to load, pass those paths under `config.env_paths`, `config.paths`, and `routes.paths` respectively.

## Composition Responsibilities

This package provides loaders (`Quill\Loaders\ConfigurationFilesLoader`, `Quill\Loaders\DotEnvLoader`, `Quill\Loaders\RouteFilesLoader`) and contracts, but it is up to your repository to decide **when** they run. A typical consumer will:

1. Define its own filesystem layout (`config/*.php`, `routes/**/*.php`, `.env*`).
2. Call `QuillFactory` with explicit paths and per-environment overrides.
3. Supply runtime adapters (PSR-7 factories, event loop, web server) and wire them to `$app->handle($psrRequest)` or `$app->processRequest($psrRequest)`.
4. Register application services via the `bindings`, `singletons`, or `boot` hooks instead of touching internal container state.

## Contracts & Extension Points

The following contracts form the public boundary that other repositories can depend on (see the boundary doc for the authoritative list):

- Container & lifecycle: `Quill\Contracts\ApplicationInterface`, `Quill\Contracts\Container\ContainerInterface`.
- Configuration: `Quill\Contracts\Configuration\ConfigurationInterface` plus the loaders mentioned above.
- HTTP: `Quill\Contracts\Request\RequestInterface`, `Quill\Contracts\Request\RequestFactoryInterface`, `Quill\Contracts\Response\ResponseInterface`, `Quill\Contracts\Response\ResponseSenderInterface`.
- Routing & middleware: `Quill\Contracts\Router\RouterInterface`, `RouteInterface`, `RouteGroupInterface`, `RouteStoreInterface`, `MiddlewareStoreInterface`, `Quill\Contracts\Middleware\MiddlewarePipelineInterface`, `Quill\Contracts\Middleware\MiddlewareFactoryInterface`.
- Error handling & support: `Quill\Contracts\ErrorHandler\ErrorHandlerInterface`, `Quill\Contracts\Support\PathResolverInterface`.

All extension takes place through those contracts: register singletons/bindings, run configuration overrides, or contribute routes/middleware with the exposed router APIs.

## Runtime Expectations

`quill-framework` no longer couples to Nyholm or any specific server runtime. Tests rely on Nyholm inside `require-dev` purely to create PSR-7 requests; consumers are free to swap in any PSR-7 implementation or server runner as long as they feed PSR-7 messages into the application and honour the interfaces above.

The upcoming `quill-app` repository will ship the default runtime entrypoints, DX tooling, and scaffolding expected by most projects. Until then, this package focuses exclusively on the reusable framework surface.
