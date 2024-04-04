<?php

declare(strict_types=1);

namespace Quill\Enum\Http;

use Quill\Enum\Steroids;

enum HttpCode: int
{
    use Steroids;

    case OK = 200;

    case NOT_FOUND = 404;
}
