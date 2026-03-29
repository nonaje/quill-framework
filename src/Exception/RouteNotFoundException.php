<?php

declare(strict_types=1);

namespace Quill\Exception;

use Quill\Enums\Http\HttpCode;

final class RouteNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            message: "Route not found",
            code: HttpCode::NOT_FOUND->value
        );
    }
}
