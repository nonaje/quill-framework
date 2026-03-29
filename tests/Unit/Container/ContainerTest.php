<?php

use Quill\Container\Container;
use Quill\Container\Exception\CircularDependencyException;
use Quill\Container\Exception\ServiceNotFoundException;
use Quill\Contracts\Container\ContainerInterface;

test('container resolves unique transients per application instance', function (): void {
    $first = new Container();
    $second = new Container();

    $first->register('foo', fn () => (object) ['id' => uniqid()]);

    expect($first->has('foo'))->toBeTrue()
        ->and($second->has('foo'))->toBeFalse();
});

test('container resolves singletons once and refreshes bindings', function (): void {
    $container = new Container();
    $container->singleton('time', fn () => (object) ['value' => microtime(true)]);

    $first = $container->get('time');
    $second = $container->get('time');

    expect($first)->toBe($second);

    $container->refresh('time', fn () => (object) ['value' => microtime(true)]);

    $third = $container->get('time');

    expect($third)->not->toBe($first);
});

test('container throws when resolving unknown services', function (): void {
    $container = new Container();

    $container->get('unknown');
})->throws(ServiceNotFoundException::class);

test('container throws on circular dependencies', function (): void {
    $container = new Container();

    $container->singleton('a', static fn (ContainerInterface $container) => $container->get('b'));
    $container->singleton('b', static fn (ContainerInterface $container) => $container->get('a'));

    $container->get('a');
})->throws(CircularDependencyException::class);
