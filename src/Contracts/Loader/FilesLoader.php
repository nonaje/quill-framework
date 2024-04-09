<?php

declare(strict_types=1);

namespace Quill\Contracts\Loader;

use Quill\Exceptions\FileNotFoundException;

interface FilesLoader
{
    /**
     * @throws FileNotFoundException
     */
    public function loadFiles(string ...$filenames): void;
}