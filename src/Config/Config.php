<?php

declare(strict_types=1);

namespace Quill\Config;

use LogicException;
use Quill\Support\Helpers\Helpers;
use Quill\Support\Pattern\Singleton;

class Config extends Singleton
{
    private string $file;

    private array $items = [];

    protected function __construct(
        private readonly Parser $parser
    )
    {
        parent::__construct();

        $this->file('config');
    }

    public function file(string $filename): self
    {
        if (!str_starts_with($filename, '/')) {
            $filename = '/' . $filename;
        }

        $filename = Helpers::projectPath() . "$filename";

        if (!file_exists($filename)) {
            throw new LogicException('Please provide a valid config file');
        }

        $this->file = $filename;

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->parser->parse($key);

        $this->file('config/' . $this->parser->first() . '.php');

        $this->items = require $this->file;

        return $this->traverseTreeThroughFile() ?? $default;
    }

    private function traverseTreeThroughFile(): mixed
    {
        $value = null;

        if ($this->parser->count() === 1) {
            return $this->items[$this->parser->first()];
        }

        foreach (array_slice($this->parser->tree(), 1) as $pointer) {
            $value = $this->items[$pointer] ?? $value[$pointer] ?? null;
        }

        return $value;
    }
}
