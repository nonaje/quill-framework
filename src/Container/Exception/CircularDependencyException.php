<?php

declare(strict_types=1);

namespace Quill\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Quill\Enums\Http\HttpCode;

final class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    /**
     * @param list<string> $stack
     */
    public function __construct(array $stack)
    {
        parent::__construct(
            sprintf('Circular dependency detected: %s', implode(' -> ', $stack)),
            HttpCode::INTERNAL_SERVER_ERROR->value
        );
    }
}
