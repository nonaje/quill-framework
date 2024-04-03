<?php

declare(strict_types=1);

namespace Quill\Exceptions\Http;

use Quill\Enum\HttpCode;
use RuntimeException;

class RouteNotFound extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            message: 'The specified route does not exists',
            code: HttpCode::NOT_FOUND->value
        );
    }
}