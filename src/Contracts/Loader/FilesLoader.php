<?php

declare(strict_types=1);

namespace Quill\Contracts\Loader;

interface FilesLoader
{
    /**
     * Loads and processes the provided list of file paths.
     *
     * This method takes one or more file paths as arguments and handles their loading
     * according to the implementation details. Each file should be passed as a string
     * representing the path to the file.
     *
     * @param string ...$filenames One or more file paths to be loaded.
     * @return void
     */
    public function load(string ...$filenames): void;
}
