<?php

namespace Quill\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Quill\Enums\Http\HttpCode;

class SingletonAlreadyRegisteredException extends Exception implements ContainerExceptionInterface
{
    public function __construct(string $serviceId)
    {
        parent::__construct("Singleton [$serviceId] is already registered.", HttpCode::SERVER_ERROR->value);
    }
}
