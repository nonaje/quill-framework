<?php

declare(strict_types=1);

namespace Quill\Support\Dot;

class Parser
{
    private string $key = '';
    private array $list = [];

    public function parse(string $key, string $separator = '.'): self
    {
        $this->key = $key;

        if (!str_contains($key, $separator)) {
            $this->list = [$key];
            return $this;
        }

        $this->list = explode($separator, $this->key);

        return $this;
    }

    public function key(string $key): string
    {
        return $this->key;
    }

    public function list(): array
    {
        return $this->list;
    }

    public function first(): string
    {
        return $this->list[0];
    }

    public function count(): int
    {
        return count($this->list);
    }
}
