# Framework ↔ App Boundary

This note fulfils OpenSpec tasks 1.1 and 1.2 for `separate-framework-from-app-starter`. It inventories the responsibilities that remain inside `quill-framework` and documents the canonical API surface that other repositories (starting with `quill-app`) can rely on.

## Responsibility Inventory

| Concern | `quill-framework` owns | Future `quill-app` owns |
| --- | --- | --- |
| **Bootstrap** | Programmatic factories (`QuillFactory`) that assemble the container, router, middleware pipeline, request/response contracts and runtime wiring from explicit configuration arrays. | Concrete entrypoints (e.g. `public/index.php`), worker scripts, and any CLI that decides when/how the factory is executed. |
| **Configuration** | Core config objects (`ConfigurationInterface`), loaders/utilities that transform arrays or files passed in at bootstrap. | Filesystem conventions (`config/*.php`, `.env`), env discovery, default config sets and DX helpers for editing them. |
| **Routing & Middleware** | Router engine, middleware pipeline, contracts (`RouterInterface`, `MiddlewarePipelineInterface`, etc.) and the ability to register routes/middleware via callbacks provided to the framework. | Authoring the actual route files, grouping, middleware lists that a concrete app ships with, plus HTTP domain logic. |
| **HTTP Contracts** | Request/response abstractions, response sender contract, error handler interfaces, DI bindings for any PSR actors. | Concrete HTTP runtime (PSR-7/HTTP factories, server runner choice, Nyholm dependency decisions) and transport-specific concerns. |
| **Container & Services** | DI container implementation, binding helpers, base singletons shared by framework features. | Application-specific services, feature modules, observers, plus any optional providers that only live in app land. |
| **DX & Scaffolding** | Only the contracts/utilities strictly required for reuse. | Project structure, coding standards, starter tests, IDE tooling, and future generators.

## Canonical Public API

This section describes the target canonical API for the refactor tracked by `separate-framework-from-app-starter`. It is the public surface other repositories should depend on as this change is implemented.

### Programmatic bootstrap

The `Quill\Factory\QuillFactory` class is the only supported entry point:

- `QuillFactory::make(string $root, array $options = []): ApplicationInterface` returns a fresh application/container each time.
- `QuillFactory::shared(string $root, array $options = []): ApplicationInterface` caches one instance per root; call `QuillFactory::forget(string $root): void` when you need to reset it (tests, workers).
- `QuillFactory::container(string $root): ContainerInterface` exposes the DI container for advanced composition once a shared application has been bootstrapped for that root.
- `QuillFactory::useDefaultRoot(string $root): void` lets helper functions such as `app()` resolve the default container without re-passing the path.

Bootstrap options are plain arrays. The canonical keys for this refactor are:

- `boot` (callable) – a composition hook where the consumer finishes wiring config, routes, runtime adapters, or any app-specific services after the framework registers its base services.
- `bindings` (array<string, callable>) – transient container definitions registered by the consumer on top of the framework defaults.
- `singletons` (array<string, callable>) – shared services that override or extend the framework defaults for one application instance.
- `config.defaults` (array) – base configuration values merged into the framework configuration object.
- `config.overrides` (array) – app-provided values merged after defaults inside the framework configuration object.

Filesystem loaders such as `ConfigurationFilesLoader`, `RouteFilesLoader`, and `DotEnvLoader` remain reusable utilities, but their invocation belongs to the consumer bootstrap instead of the framework's implicit default flow.

### Contracts the framework guarantees

The following names are the stable, importable contracts another repo can depend on. Anything else is internal until promoted here.

- Container & application: `Quill\Contracts\Container\ContainerInterface`, `Quill\Contracts\ApplicationInterface`.
- Config: `Quill\Contracts\Configuration\ConfigurationInterface`.
- HTTP layer: `Quill\Contracts\Request\RequestInterface`, `Quill\Contracts\Response\ResponseInterface`, `Quill\Contracts\Response\ResponseSenderInterface`.
- Routing & middleware: `Quill\Contracts\Router\RouterInterface`, `RouteInterface`, `RouteGroupInterface`, `RouteStoreInterface`, `MiddlewareStoreInterface`, `MiddlewaresInterface`, `Quill\Contracts\Middleware\MiddlewarePipelineInterface`, `Quill\Contracts\Middleware\MiddlewareFactoryInterface`.
- Error handling: `Quill\Contracts\ErrorHandler\ErrorHandlerInterface`.
- Support utilities: `Quill\Contracts\Support\PathResolverInterface`, `Quill\Contracts\Loader\FilesLoader`.

Any future contract must be recorded in this section before other repos rely on it.

### Extension points

- **Container bindings** – Consumers can pass closures under `bindings` and `singletons`, or interact directly with `ContainerInterface::register()`, `ContainerInterface::singleton()`, and `ContainerInterface::refresh()` from their own composition root.
- **Configuration overrides** – Provide config arrays via loaders or direct injection; the framework only merges the data it understands, so app-level keys are allowed as long as consumers read them later.
- **Router composition** – Route files (returned closures) are the supported mechanism to add routes, route-level middleware, or nested groups.
- **Middleware pipeline** – Register application middleware via `ApplicationInterface::use()` and attach per-route middleware through the router APIs contained in `RouterInterface`.
- **Runtime adapters** – The request, response, and sender contracts accept any implementation that honours the interfaces, enabling adapters to swap Nyholm or integrate with workers/tasks.

Use these hooks from `quill-app` or any other composition root instead of reaching into internal namespaces.
