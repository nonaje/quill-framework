<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum HttpMethod: string
{
    use Asteroids;

    case GET = 'GET';

    case POST = 'POST';

    case PUT = 'PUT';

    case PATCH = 'PATCH';

    case DELETE = 'DELETE';
}