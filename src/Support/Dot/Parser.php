<?php

declare(strict_types=1);

namespace Quill\Support\Dot;

use Quill\Contracts\Support\DotNotationParserInterface;
use Quill\Support\Traits\Singleton;

final class Parser implements DotNotationParserInterface
{
    use Singleton;

    private string $key = '';

    private array $list = [];

    protected function __construct() {}

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function list(): array
    {
        return $this->list;
    }

    /**
     * @inheritDoc
     */
    public function first(): string
    {
        return $this->list[0];
    }

    /**
     * @inheritDoc
     */
    public function last(): string
    {
        return $this->list[$this->count() - 1];
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->list);
    }
}
