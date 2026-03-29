<?php

use Nyholm\Psr7\ServerRequest;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\ErrorHandler\ErrorHandlerInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Response\ResponseSenderInterface;
use Quill\Enums\Http\HttpCode;
use Quill\Factory\QuillFactory;
use Quill\Handler\Error\JsonErrorHandler;
use RuntimeException;

beforeEach(function (): void {
    QuillFactory::forget();
});

test('applications can register routes and handle requests without global helpers', function (): void {
    $app = QuillFactory::make(fixture_root(), [
        'routes' => ['auto' => false],
    ]);

    $app->get('/health', function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $response->status(HttpCode::OK)->json(['ok' => true]);
    });

    $response = $app->handle(new ServerRequest('GET', '/health'));
    $payload = json_decode((string) $response->getBody(), true);

    expect($response->getStatusCode())->toBe(HttpCode::OK->value)
        ->and($payload)->toMatchArray(['ok' => true]);
});

test('service overrides stay isolated per application instance', function (): void {
    $customApp = QuillFactory::make(fixture_root(), [
        'singletons' => [
            ErrorHandlerInterface::class => static fn (ContainerInterface $container): ErrorHandlerInterface => new class(
                $container->get(ResponseInterface::class),
                $container->get(ResponseSenderInterface::class)
            ) extends JsonErrorHandler {
                protected function toResponse(): ResponseInterface
                {
                    /** @var ResponseInterface $response */
                    $response = parent::toResponse()->withHeader('X-Error-Handler', 'custom');

                    return $response;
                }
            },
        ],
    ]);

    $defaultApp = QuillFactory::make(fixture_root());

    foreach ([$customApp, $defaultApp] as $app) {
        $app->get('/explode', function (): never {
            throw new RuntimeException('boom');
        });
    }

    $customResponse = $customApp->handle(new ServerRequest('GET', '/explode'));
    $defaultResponse = $defaultApp->handle(new ServerRequest('GET', '/explode'));

    expect($customResponse->getHeaderLine('X-Error-Handler'))->toBe('custom')
        ->and($defaultResponse->getHeaderLine('X-Error-Handler'))->toBe('');
});

test('response sender overrides stay isolated per application instance', function (): void {
    $firstProbe = new class {
        public int $calls = 0;
        public array $statuses = [];
    };

    $secondProbe = new class {
        public int $calls = 0;
        public array $statuses = [];
    };

    $firstApp = QuillFactory::make(fixture_root(), [
        'singletons' => [
            ResponseSenderInterface::class => static fn () => new class($firstProbe) implements ResponseSenderInterface {
                public function __construct(private object $probe)
                {
                }

                public function send(\Psr\Http\Message\ResponseInterface $response): void
                {
                    $this->probe->calls++;
                    $this->probe->statuses[] = $response->getStatusCode();
                }
            },
        ],
        'routes' => ['auto' => false],
    ]);

    $secondApp = QuillFactory::make(fixture_root(), [
        'singletons' => [
            ResponseSenderInterface::class => static fn () => new class($secondProbe) implements ResponseSenderInterface {
                public function __construct(private object $probe)
                {
                }

                public function send(\Psr\Http\Message\ResponseInterface $response): void
                {
                    $this->probe->calls++;
                    $this->probe->statuses[] = $response->getStatusCode();
                }
            },
        ],
        'routes' => ['auto' => false],
    ]);

    foreach ([$firstApp, $secondApp] as $app) {
        $app->get('/health', function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
            return $response->status(HttpCode::OK)->json(['ok' => true]);
        });
    }

    $firstApp->processRequest(new ServerRequest('GET', '/health'));
    $secondApp->processRequest(new ServerRequest('GET', '/health'));

    expect($firstProbe->calls)->toBe(1)
        ->and($secondProbe->calls)->toBe(1)
        ->and($firstProbe->statuses)->toBe([HttpCode::OK->value])
        ->and($secondProbe->statuses)->toBe([HttpCode::OK->value]);
});
