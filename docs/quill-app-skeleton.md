# quill-app Skeleton (Preview)

`quill-app` will be the official starter repository that consumes `quill-framework`. Its job is to provide the runtime, DX tooling, and default project structure while keeping the framework reusable. This note captures the minimum responsibilities for that starter so work on both repos can proceed independently.

## High-Level Goals

- Expose a ready-to-run HTTP entrypoint (e.g. `public/index.php` or a worker script) that boots the framework via `QuillFactory` using explicit options.
- Own the filesystem conventions for applications: where configuration files live, how routes are grouped, and how environment files are discovered.
- Ship DX helpers (example tests, bootstrap scripts, Composer scripts) that prove how consumers should extend the framework boundary without reaching into internals.

## Minimal Layout

```
quill-app/
├── bootstrap/app.php          # Assembles options, loaders, and returns the configured Quill app
├── public/index.php           # Web entrypoint that feeds PSR-7 requests into the app
├── config/*.php               # Application configuration files consumed via loaders
├── routes/*.php               # Route definitions that register HTTP endpoints
├── .env.example               # Documented env inputs; actual .env stays user-specific
├── tests/                     # Starter feature/unit tests using quill-framework contracts
└── composer.json              # Requires quill-framework plus any runtime PSR-7/server packages
```

Nothing inside `quill-framework` assumes this structure—the starter simply codifies a sensible default so new projects can `git clone` and start coding.

## Responsibilities

1. **Runtime bootstrap** – Choose and configure the HTTP runtime (e.g. Nyholm+PHP-FPM today, workers later) and call `$app->handle($psrRequest)`.
2. **Configuration & env discovery** – Decide which paths are passed to `config.paths`, `config.env_paths`, and provide any default `config.defaults`/`config.overrides` arrays for the framework to merge.
3. **Route & middleware registration** – Provide the actual route files, middleware registration scripts, and any domain-specific bindings.
4. **Application services** – Register shared singletons/bindings for things like loggers, database clients, feature modules, etc., using the hooks exposed by the framework.
5. **DX surface** – Publish documentation that points back to `docs/framework-boundary.md`, demonstrate how to run tests, lint, and deploy, and include the guidance for rotating secrets/env files.

The starter must stay on its side of the boundary: anything meant to be reusable across apps should live (or move) into `quill-framework`. Conversely, opinionated project scaffolding, conventions, or runtime glue should remain inside `quill-app` so framework consumers can opt in or out.
