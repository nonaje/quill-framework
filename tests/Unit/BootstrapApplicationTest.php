<?php

use Quill\Factory\QuillFactory;
use Quill\Quill;

test('make quill')
    ->expect(QuillFactory::make())
    ->toBeInstanceOf(Quill::class);
