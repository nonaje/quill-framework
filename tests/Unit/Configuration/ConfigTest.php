<?php

use Quill\Configuration\Config;

test('config resolves nested values with dot notation', function (): void {
    $config = new Config(['app' => ['name' => 'Quill', 'debug' => false]]);

    expect($config->get('app.name'))->toBe('Quill')
        ->and($config->get('app.debug'))->toBeFalse()
        ->and($config->get('app.missing', 'fallback'))->toBe('fallback');
});

test('config merges defaults files env and overrides deterministically', function (): void {
    $config = new Config(['app' => ['name' => 'Default', 'debug' => false]]);
    $config->merge(['app' => ['debug' => true]]);
    $config->merge(['env' => ['app_env' => 'staging']]);
    $config->merge(['app' => ['name' => 'Override']]);

    expect($config->get('app.name'))->toBe('Override')
        ->and($config->get('app.debug'))->toBeTrue()
        ->and($config->get('env.app_env'))->toBe('staging');
});
