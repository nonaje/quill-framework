<?php

declare(strict_types=1);

namespace Quill\Config;

use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Support\DotNotationParserInterface;
use Quill\Support\Traits\Singleton;

class Config implements ConfigurationInterface
{
    use Singleton;

    private array $items = [];

    protected function __construct(
        private readonly DotNotationParserInterface $parser
    )
    {
    }

    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $key = strtolower($key);
        $this->parser->parse($key);

        $value = null;

        foreach ($this->parser->list() as $pointer) {
            $value = $this->items[$pointer] ?? $value[$pointer] ?? null;
        }

        return $value ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $key = strtolower($key);
        $this->parser->parse($key);

        $items = &$this->items;

        foreach ($this->parser->list() as $_key) {
            if (!isset($items[$_key]) || !is_array($items[$_key])) {
                $items[$_key] = [];
            }

            $items = &$items[$_key];
        }

        $items = $value;
    }
}
