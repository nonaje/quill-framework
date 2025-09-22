<?php

declare(strict_types=1);

namespace Quill\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Quill\Enums\Http\HttpCode;

class ServiceNotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct(string $serviceId)
    {
        parent::__construct("Service [$serviceId] not found.", HttpCode::SERVER_ERROR->value);
    }
}
