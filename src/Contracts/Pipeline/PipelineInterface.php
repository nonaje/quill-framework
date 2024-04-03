<?php

declare(strict_types=1);

namespace Quill\Contracts\Pipeline;

use Closure;

interface PipelineInterface
{
    public function send(mixed $toSend): PipelineInterface;

    public function via(array $pipes): PipelineInterface;

    public function method(string $method): PipelineInterface;

    public function exec(): mixed;
}