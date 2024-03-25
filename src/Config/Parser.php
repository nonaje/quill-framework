<?php

declare(strict_types=1);

namespace Quill\Config;

use LogicException;

class Parser
{
    private string $key = '';
    private array $tree = [];

    public function parse(string $key, string $separator = '.'): void
    {
        $this->key = $key;

        if (! str_contains($key, $separator)) {
            $this->tree = [$key];
            return;
        }

        $this->tree = explode($separator, $this->key);

        dd($this->tree);
//        $file = self::CONFIG_PATH . "/$exploded[0].php";

//        $this->tree = explode('.',  $exploded[1]);

//        return file_exists($file) ? require $file : null;
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
