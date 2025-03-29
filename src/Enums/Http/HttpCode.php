<?php

declare(strict_types=1);

namespace Quill\Enums\Http;

use Quill\Enums\Steroids;

enum HttpCode: int
{
    use Steroids;

    // 2xx
    case OK = 200;

    // 4xx
    case NOT_FOUND = 404;
    case FORBIDDEN = 403;
    case UNAUTHORIZED = 401;

    // 5xx
    case SERVER_ERROR = 500;
}
