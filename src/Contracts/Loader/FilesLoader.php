<?php

namespace Quill\Contracts\Loader;

use InvalidArgumentException;

interface FilesLoader
{
    /**
     * @throws InvalidArgumentException
     */
    public function loadFiles(array $filenames): void;
}