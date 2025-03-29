<?php

declare(strict_types=1);

namespace Quill\Enums\Http;

use Quill\Enums\Steroids;

enum HttpMethod: string
{
    use Steroids;

    case GET = 'GET';

    case POST = 'POST';

    case PUT = 'PUT';

    case PATCH = 'PATCH';

    case DELETE = 'DELETE';

    case HEAD = 'HEAD';

    case OPTIONS = 'OPTIONS';
}
