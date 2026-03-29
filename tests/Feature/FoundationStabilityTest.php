<?php

use InvalidArgumentException;
use Nyholm\Psr7\ServerRequest;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Support\PathResolverInterface;
use Quill\Factory\QuillFactory;

beforeEach(function (): void {
    QuillFactory::forget();
    $this->temporaryRoots = [];
});

afterEach(function (): void {
    foreach ($this->temporaryRoots as $root) {
        delete_directory($root);
    }

    QuillFactory::forget();
});

test('bootstrap merges defaults config env and overrides in deterministic order', function (): void {
    $root = create_test_application_root($this, [
        '.env' => "APP_ENV=production\nAPP_FLAG=true\n",
        'config/app.php' => <<<'PHP'
<?php

declare(strict_types=1);

return [
    'name' => 'Configured App',
    'debug' => false,
    'feature' => [
        'source' => 'config',
    ],
    'middlewares' => [],
    'lifecycle' => [
        \Quill\Middleware\ExceptionHandlingMiddleware::class,
        \Quill\Middleware\RouteFinderMiddleware::class,
        \Quill\Middleware\ExecuteGlobalUserDefinedMiddlewares::class,
        \Quill\Middleware\ExecuteRouteMiddlewares::class,
    ],
];
PHP,
    ]);

    QuillFactory::make($root, [
        'config' => [
            'defaults' => [
                'app' => [
                    'name' => 'Default App',
                    'debug' => false,
                    'feature' => [
                        'source' => 'default',
                        'enabled' => false,
                    ],
                ],
            ],
            'overrides' => [
                'app' => [
                    'debug' => true,
                    'feature' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ],
    ]);

    $container = QuillFactory::container($root);
    $config = $container->get(ConfigurationInterface::class);
    $path = $container->get(PathResolverInterface::class);

    expect($config->get('app.name'))->toBe('Configured App')
        ->and($config->get('app.debug'))->toBeTrue()
        ->and($config->get('app.feature.source'))->toBe('config')
        ->and($config->get('app.feature.enabled'))->toBeTrue()
        ->and($config->get('env.app_env'))->toBe('production')
        ->and($config->get('env.app_flag'))->toBeTrue()
        ->and($path->toFile('routes'))->toBe($root . '/routes');
});

test('bootstrap can skip dotenv loading and auto-load nested grouped route files', function (): void {
    $root = create_test_application_root($this, [
        '.env' => "APP_ENV=production\n",
        'routes/api.php' => <<<'PHP'
<?php

declare(strict_types=1);

use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouterInterface;

return static function (RouterInterface $router): void {
    $router->group('/api', static function (RouterInterface $router): void {
        $router->group('/v1', static function (RouterInterface $router): void {
            $router->get('/health/:service', static function (RequestInterface $request, ResponseInterface $response): ResponseInterface {
                return $response->json([
                    'service' => $request->route('service'),
                    'status' => 'ok',
                ]);
            });
        });
    });
};
PHP,
    ]);

    $app = QuillFactory::make($root, [
        'config' => [
            'load_dotenv' => false,
        ],
    ]);

    $response = $app->handle(new ServerRequest('GET', '/api/v1/health/core'));
    $payload = json_decode((string) $response->getBody(), true);
    $config = QuillFactory::container($root)->get(ConfigurationInterface::class);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toMatchArray([
            'service' => 'core',
            'status' => 'ok',
        ])
        ->and($config->get('env.app_env'))->toBeNull();
});

test('bootstrap fails explicitly when the application root is invalid', function (): void {
    QuillFactory::make('/tmp/quill-missing-root-' . uniqid('', true));
})->throws(InvalidArgumentException::class);

/**
 * @param array<string, string> $files
 */
function create_test_application_root(object $testCase, array $files = []): string
{
    $root = sys_get_temp_dir() . '/quill-foundation-' . uniqid('', true);
    mkdir($root, 0777, true);
    mkdir($root . '/config', 0777, true);
    mkdir($root . '/routes', 0777, true);

    $defaults = [
        '.env' => "APP_ENV=testing\n",
        'config/app.php' => <<<'PHP'
<?php

declare(strict_types=1);

return [
    'name' => 'Temporary App',
    'middlewares' => [],
    'lifecycle' => [
        \Quill\Middleware\ExceptionHandlingMiddleware::class,
        \Quill\Middleware\RouteFinderMiddleware::class,
        \Quill\Middleware\ExecuteGlobalUserDefinedMiddlewares::class,
        \Quill\Middleware\ExecuteRouteMiddlewares::class,
    ],
];
PHP,
        'routes/api.php' => <<<'PHP'
<?php

declare(strict_types=1);

use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouterInterface;

return static function (RouterInterface $router): void {
    $router->get('/health', static fn ($request, ResponseInterface $response) => $response->json([
        'status' => 'ok',
    ]));
};
PHP,
    ];

    foreach (array_replace($defaults, $files) as $relativePath => $contents) {
        $fullPath = $root . '/' . $relativePath;
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($fullPath, $contents);
    }

    $testCase->temporaryRoots[] = $root;

    return $root;
}

function delete_directory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $items = scandir($directory);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . '/' . $item;

        if (is_dir($path)) {
            delete_directory($path);
            continue;
        }

        @unlink($path);
    }

    @rmdir($directory);
}
