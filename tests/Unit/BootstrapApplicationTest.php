<?php

test('make quill')
    ->expect(\Quill\Factory\QuillFactory::make())
    ->toBeInstanceOf(\Quill\Quill::class);
