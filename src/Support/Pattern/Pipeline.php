<?php

declare(strict_types=1);

namespace Quill\Support\Pattern;

use Closure;
use LogicException;
use Quill\Contracts\Pipeline\PipelineInterface;

final class Pipeline implements PipelineInterface
{
    private array $toSend;

    private array $pipes = [];

    private string $method = '__invoke';

    public function send(...$toSend): PipelineInterface
    {
        $this->toSend = func_get_args();

        return $this;
    }

    public function using(array $pipes): PipelineInterface
    {
        $this->pipes = $pipes;

        return $this;
    }

    public function method(string $method): PipelineInterface
    {
        $this->method = $method;

        return $this;
    }

    public function exec(): mixed
    {
        $this->assert();

        $start = array_reduce(
            array: array_reverse($this->pipes),
            callback: $this->resolve(),
            initial: fn ($toSend) => $toSend
        );

        return $start($this->toSend);
    }

    private function resolve(): Closure
    {
        return function ($previous, $current) {
            return function (...$toSend) use ($previous, $current) {
                $parameters = func_get_args();
                $parameters[] = $previous;
                $parameters = array_flatten($parameters);

                if (is_object($current)) {
                    return $current->{$this->method}(...$parameters);
                }

                if (is_callable($current)) {
                    return $current(...$parameters);
                }

                return (new $current)->{$this->method}(...$parameters);
            };
        };
    }

    private function assert(): void
    {
        if (empty($this->method)) {
            throw new LogicException('Pipeline via method cannot be empty');
        }

        if (count($this->pipes) === 0) {
            throw new LogicException('There must be at least one pipe defined');
        }

        foreach ($this->pipes as $pipe) {

            if (is_callable($pipe)) {
                continue;
            }

            if (is_object($pipe)) {
                if (!class_exists(get_class($pipe))) {
                    throw new LogicException('Pipelines must be a valid class');
                }

                if (!method_exists($pipe, $this->method)) {
                    throw new LogicException('Undefined method ' . self::class . '@' . $this->method);
                }

                continue;
            }

            if (is_array($pipe)) {
                throw new LogicException('Pipeline elements cannot be array');
            }

            if (!class_exists($pipe)) {
                throw new LogicException('Pipelines must be a valid class');
            }
        }
    }
}