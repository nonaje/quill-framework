<?php

declare(strict_types=1);

namespace Quill\Contracts\Loader;

use InvalidArgumentException;

interface FilesLoader
{
    /**
     * @throws InvalidArgumentException
     */
    public function loadFiles(array $filenames): void;
}