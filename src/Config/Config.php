<?php

declare(strict_types=1);

namespace Quill\Config;

use Quill\Support\Dot\Parser;
use Quill\Support\Pattern\Singleton;

class Config extends Singleton
{
    private array $items = [];

    protected function __construct(
        private readonly Parser $parser
    )
    {
        parent::__construct();
    }

    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $key = strtolower($key);

        $this->parser->parse($key);

        return $this->searchInItems() ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $key = strtolower($key);

        $result = [];
        $reference = &$result;

        foreach ($this->parser->parse($key)->list() as $key) {
            $reference[$key] = [];
            $reference = &$reference[$key];
        }

        $reference = $value;
        unset($reference);
        $this->items = array_merge_recursive($result, $this->items);
    }

    private function searchInItems(): mixed
    {
        $value = null;

        if ($this->parser->count() === 1) {
            return $this->items[$this->parser->first()] ?? null;
        }

        foreach (array_slice($this->parser->list(), 1) as $pointer) {
            $value = $this->items[$this->parser->first()][$pointer] ?? $value[$pointer] ?? null;
        }

        return $value;
    }
}
