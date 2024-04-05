<?php

declare(strict_types=1);

namespace Quill\Contracts\Path;

use Quill\Exceptions\FileNotFoundException;

interface PathFinderInterface
{
    /**
     * @throws FileNotFoundException
     */
    public function setApplicationPath(string $path): void;

    public function quillFile(string $filename): string;

    public function quillPath(): string;

    public function configFile(string $filename): string;

    public function applicationFile(string $filename): string;

    public function routeFile(string $filename): string;
}
