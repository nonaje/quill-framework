<?php

declare(strict_types=1);

namespace Quill\Configuration;

use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Support\DotNotationParser;

class Config implements ConfigurationInterface
{
    use DotNotationParser;

    public function __construct(private array $items = [])
    {
    }

    /** @inheritDoc */
    public function all(): array
    {
        return $this->items;
    }

    /** @inheritDoc */
    public function get(string $key, mixed $default = null): mixed
    {
        $segments = $this->dotNotationToArray($key);

        if ($segments === []) {
            return $default;
        }

        $value = $this->items;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
                continue;
            }

            return $default;
        }

        return $value;
    }

    /** @inheritDoc */
    public function push(string $key, mixed $value): ConfigurationInterface
    {
        $segments = $this->dotNotationToArray($key);

        if ($segments === []) {
            return $this;
        }

        $items = &$this->items;
        $lastSegment = array_pop($segments);

        if ($lastSegment === null) {
            return $this;
        }

        foreach ($segments as $segment) {
            if (!isset($items[$segment]) || !is_array($items[$segment])) {
                $items[$segment] = [];
            }

            $items = &$items[$segment];
        }

        if (!isset($items[$lastSegment])) {
            $items[$lastSegment] = [];
        }

        if (!is_array($items[$lastSegment])) {
            $items[$lastSegment] = [$items[$lastSegment]];
        }

        $items[$lastSegment][] = $value;

        return $this;
    }

    /** @inheritDoc */
    public function put(string $key, mixed $value): ConfigurationInterface
    {
        $segments = $this->dotNotationToArray($key);

        if ($segments === []) {
            return $this;
        }

        $items = &$this->items;
        $lastSegment = array_pop($segments);

        if ($lastSegment === null) {
            return $this;
        }

        foreach ($segments as $segment) {
            if (!isset($items[$segment]) || !is_array($items[$segment])) {
                $items[$segment] = [];
            }

            $items = &$items[$segment];
        }

        $items[$lastSegment] = $value;

        return $this;
    }

    /** @inheritDoc */
    public function merge(array ...$repositories): ConfigurationInterface
    {
        foreach ($repositories as $repository) {
            if ($repository === []) {
                continue;
            }

            $this->items = $this->mergeRecursive($this->items, $repository);
        }

        return $this;
    }

    private function mergeRecursive(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeRecursive($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }
}
