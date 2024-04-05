<?php

declare(strict_types=1);

namespace Quill\Exceptions;

use Throwable;

class FileNotFoundException extends \Exception
{
    public function __construct(
        string $filename,
        ?Throwable $previous = null
    )
    {
        $message = "The specified filename: '$filename' does not exists.";

        parent::__construct($message);
    }
}