# Quill Framework

Lightweight HTTP APIs that boot around a PSR-7/Nyholm runtime and a small router.

## Canonical Bootstrap

```php
use Quill\Factory\QuillFactory;

require __DIR__ . '/vendor/autoload.php';

$root = __DIR__;
QuillFactory::useDefaultRoot($root);

$app = QuillFactory::shared($root, [
    'config' => [
        'paths' => [$root . '/config'],
    ],
    'routes' => [
        'paths' => [$root . '/routes'],
    ],
]);

// optional helper, once the default root is set:
$app = app();
```

- `QuillFactory::make($root)` creates a fresh instance every call.
- `QuillFactory::shared($root)` and the optional `app()` helper reuse the same container per root; call `QuillFactory::forget()` when you need a clean slate (tests, workers, etc.).
- `QuillFactory::container($root)` exposes the bootstrapped DI container for advanced composition.

## Routes

Routes live in PHP files that return a closure receiving the `RouterInterface`. Files found under the configured `routes.paths` are auto-loaded when `routes.auto` is true (default).

```php
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Response\ResponseInterface;

return static function (RouterInterface $router): void {
    $router->get('/health', static fn ($request, ResponseInterface $response) =>
        $response->json(['status' => 'ok'])
    );

    $router->group('/v1', static function (RouterInterface $router): void {
        $router->post('/users', static fn ($request, ResponseInterface $response) =>
            $response->json(['created' => true])->status(201)
        );
    });
};
```

- Supported verbs map directly to `RouterInterface::get|post|patch|put|delete` plus nested `group($prefix, $callback)` for reusable prefixes.
- Closures are the canonical and documented route handler surface for this stabilized core. Other legacy targets may still exist internally, but they are not part of the public contract of this change.
- Route callbacks receive the resolved `RequestInterface` and `ResponseInterface`. Middleware groups declared in config run before route execution; per-route middleware stacks can be assigned through the router API.

## Requests & Responses

- Requests wrap a PSR-7 `ServerRequestInterface`. The concrete `RequestInterface` supports helpers such as `->route('id')`, `->query('page')`, `->body('name')`, `->all()`, and the original PSR request via `->getPsrRequest()`.
- Responses extend `Nyholm\Psr7\Response`. Convenience helpers include `json(array $data)`, `plain(string $text)`, `html(string $html)`, plus `code(HttpCode::OK)` / `status(int $code)` and fluent `headers([...])`.
- Responses are dispatched by `ResponseSender`, so you can return any PSR-7 message as long as it implements `MessageInterface`.

## Runtime Notes

- The middleware pipeline, router, and container are internal, but PSR interfaces are used throughout: PSR-7 requests/responses (Nyholm implementation), PSR container bindings, and Nyholm streams.
- Until a future follow-up change decouples the runtime, Nyholm components remain a mandatory dependency of the current core.

## Current Limits

- Bootstrapping still requires explicit filesystem roots for config/route discovery.
- There are no global helpers such as `quill()` or `router()` anymore; stick to `QuillFactory` and the optional `app()` helper described above.
