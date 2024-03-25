<?php

declare(strict_types=1);

namespace Quill\Config;

class Parser
{
    private string $key = '';
    private array $tree = [];

    public function parse(string $key, string $separator = '.'): self
    {
        $this->key = $key;

        if (!str_contains($key, $separator)) {
            $this->tree = [$key];
            return $this;
        }

        $this->tree = explode($separator, $this->key);

        return $this;
    }

    public function key(string $key): string
    {
        return $this->key;
    }

    public function tree(): array
    {
        return $this->tree;
    }

    public function first(): string
    {
        return $this->tree[0];
    }

    public function count(): int
    {
        return count($this->tree);
    }
}
