<?php

use Closure;
use LogicException;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouterInterface;
use Quill\Enums\Http\HttpCode;
use Quill\Factory\QuillFactory;
use RuntimeException;

beforeEach(function (): void {
    QuillFactory::forget();
});

test('router preserves its registry between dispatches', function (): void {
    $app = QuillFactory::make(fixture_root());
    $app->get('/ping', function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $response->code(HttpCode::OK)->json(['pong' => true]);
    });

    $first = $app->handle(new ServerRequest('GET', '/ping'));
    $second = $app->handle(new ServerRequest('GET', '/ping'));

    expect($first->getStatusCode())->toBe(HttpCode::OK->value)
        ->and((string) $first->getBody())->toBe((string) $second->getBody())
        ->and($app->routes())->toHaveCount(1);
});

test('router matches named parameters deterministically', function (): void {
    $app = QuillFactory::make(fixture_root());
    $app->get('/users/:user/books/:book', function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $response->code(HttpCode::OK)->json([
            'user' => $request->route('user'),
            'book' => $request->route('book'),
        ]);
    });

    $response = $app->handle(new ServerRequest('GET', '/users/9/books/42'));
    $payload = json_decode((string) $response->getBody(), true);

    expect($response->getStatusCode())->toBe(HttpCode::OK->value)
        ->and($payload)
        ->toMatchArray(['user' => '9', 'book' => '42']);
});

test('duplicate routes for the same method and path throw a logic exception', function (): void {
    $app = QuillFactory::make(fixture_root());
    $app->get('/ping', function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $response->code(HttpCode::OK)->json(['pong' => true]);
    });

    expect(fn () => $app->get('/ping', function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $response->code(HttpCode::OK);
    }))->toThrow(LogicException::class);
});

test('nested group middlewares execute in order before the route handler', function (): void {
    $app = QuillFactory::make(fixture_root());

    $middleware = static function (string $label): Closure {
        return static function (
            ServerRequestInterface $request,
            RequestHandlerInterface $handler,
            ContainerInterface $container
        ) use ($label) {
            $stack = $request->getAttribute('stack', []);
            $stack[] = $label;

            return $handler->handle($request->withAttribute('stack', $stack));
        };
    };

    $app->use($middleware('global'));

    $app->group('/api', function (RouterInterface $router) use ($middleware): void {
        $router->group('/v1', function (RouterInterface $router) use ($middleware): void {
            $router->get('/users/:id', function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
                return $response->code(HttpCode::OK)->json([
                    'stack' => $request->getPsrRequest()->getAttribute('stack', []),
                    'id' => $request->route('id'),
                ]);
            }, [$middleware('route')]);
        }, [$middleware('group:v1')]);
    }, [$middleware('group:api')]);

    $response = $app->handle(new ServerRequest('GET', '/api/v1/users/7'));
    $payload = json_decode((string) $response->getBody(), true);

    expect($payload['stack'])
        ->toBe(['global', 'group:api', 'group:v1', 'route']);
});

test('middlewares can short circuit the pipeline', function (): void {
    $app = QuillFactory::make(fixture_root());

    $routeInvoked = false;
    $middlewareInvoked = false;

    $app->use(static function (
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        ContainerInterface $container
    ) use (&$middlewareInvoked): ResponseInterface {
        $middlewareInvoked = true;

        return $container->get(ResponseInterface::class)
            ->code(HttpCode::OK)
            ->json(['short' => true]);
    });

    $app->get('/profile', function (RequestInterface $request, ResponseInterface $response) use (&$routeInvoked): ResponseInterface {
        $routeInvoked = true;

        return $response->code(HttpCode::OK)->json(['profile' => 'resolved']);
    });

    $response = $app->handle(new ServerRequest('GET', '/profile'));
    $payload = json_decode((string) $response->getBody(), true);

    expect($middlewareInvoked)->toBeTrue()
        ->and($routeInvoked)->toBeFalse()
        ->and($payload)
        ->toMatchArray(['short' => true]);
});

test('unhandled exceptions are converted into json error responses', function (): void {
    $app = QuillFactory::make(fixture_root());

    $app->get('/explode', function (): never {
        throw new RuntimeException('boom');
    });

    $response = $app->handle(new ServerRequest('GET', '/explode'));
    $payload = json_decode((string) $response->getBody(), true);

    expect($response->getStatusCode())->toBe(HttpCode::INTERNAL_SERVER_ERROR->value)
        ->and($payload['success'])->toBeFalse()
        ->and($payload['message'])->toBe('boom')
        ->and($payload)->toHaveKeys(['file', 'line', 'trace']);
});

test('error responses hide debug details in production', function (): void {
    $app = QuillFactory::make(fixture_root(), [
        'config' => [
            'overrides' => [
                'env' => ['app_env' => 'production'],
            ],
        ],
    ]);

    $app->get('/boom', function (): never {
        throw new RuntimeException('explode');
    });

    $response = $app->handle(new ServerRequest('GET', '/boom'));
    $payload = json_decode((string) $response->getBody(), true);

    expect($payload)->toMatchArray([
        'success' => false,
        'message' => 'explode',
    ]);

    expect($payload)->not->toHaveKeys(['file', 'trace']);
});
