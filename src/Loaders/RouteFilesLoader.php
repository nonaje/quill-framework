<?php

namespace Quill\Loaders;

use InvalidArgumentException;
use Quill\Contracts\Loader\FilesLoader;

class RouteFilesLoader implements FilesLoader
{
    /**
     * @inheritDoc
     */
    public function loadFiles(array $filenames): void
    {
        foreach ($filenames as $filename) {
            if (! file_exists($filename)) {
                throw new InvalidArgumentException("File: $filename does not exists");
            }

            $routes = require_once $filename;

            if (is_callable($routes)) {
                $routes($this);
            }
        }
    }
}