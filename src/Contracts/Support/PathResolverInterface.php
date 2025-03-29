<?php

declare(strict_types=1);

namespace Quill\Contracts\Support;

interface PathResolverInterface
{
    /**
     * Returns the absolute path to a file in the root path of the application.
     *
     * @param string $filename
     * @return string
     */
    public function toFile(string $filename): string;
}
