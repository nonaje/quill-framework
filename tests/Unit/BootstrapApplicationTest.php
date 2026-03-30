<?php

use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Support\PathResolverInterface;
use Quill\Factory\QuillFactory;
use Quill\Quill;

beforeEach(function (): void {
    QuillFactory::forget();
});

test('quill factory builds an application for a given root', function (): void {
    $root = fixture_root();
    $app = QuillFactory::make($root, framework_options($root));

    expect($app)->toBeInstanceOf(Quill::class);
});

test('quill factory reuses the same shared application per root', function (): void {
    $root = fixture_root();
    $first = QuillFactory::shared($root, framework_options($root));
    $second = QuillFactory::shared($root, framework_options($root));

    expect($first)->toBe($second);
});

test('quill factory produces isolated applications via make', function (): void {
    $root = fixture_root();
    $first = QuillFactory::make($root, framework_options($root));
    $second = QuillFactory::make($root, framework_options($root));

    expect($first)->not->toBe($second);
});

test('quill factory merges defaults env files and overrides deterministically', function (): void {
    $root = fixture_root();
    QuillFactory::shared($root, framework_options($root, [
        'config' => [
            'defaults' => ['app' => ['debug' => false]],
            'overrides' => ['app' => ['debug' => true]],
        ],
    ]));

    $container = QuillFactory::container($root);
    $config = $container->get(ConfigurationInterface::class);
    $path = $container->get(PathResolverInterface::class);

    expect($config->get('env.app_env'))->toBe('testing')
        ->and($config->get('app.debug'))->toBeTrue()
        ->and($path->toFile('config'))
        ->toBe($root . '/config');
});

test('global helpers reuse the explicit application context', function (): void {
    $root = fixture_root();

    $app = app($root, framework_options($root));
    $container = QuillFactory::container($root);
    $path = $container->get(PathResolverInterface::class);

    expect($app)->toBeInstanceOf(Quill::class)
        ->and($path->toFile('config/app.php'))
        ->toBe($root . '/config/app.php');
});
