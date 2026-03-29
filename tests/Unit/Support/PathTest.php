<?php

use InvalidArgumentException;
use Quill\Support\Path;

test('path normalizes roots and segments', function (): void {
    $root = fixture_root();
    $path = new Path($root);

    expect($path->toFile('config/app.php'))
        ->toBe($root . '/config/app.php')
        ->and($path->toFile('routes//api.php'))
        ->toBe($root . '/routes/api.php');
});

test('path rejects missing roots', function (): void {
    new Path('/missing/root/' . uniqid());
})->throws(InvalidArgumentException::class);
