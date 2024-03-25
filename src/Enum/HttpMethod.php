<?php

declare(strict_types=1);

namespace Quill\Enum;

enum HttpMethod: string
{
    use Steroids;

    case GET = 'GET';

    case POST = 'POST';

    case PUT = 'PUT';

    case PATCH = 'PATCH';

    case DELETE = 'DELETE';
}