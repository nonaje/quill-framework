<?php

declare(strict_types=1);

namespace Quill\Config;

use LogicException;
use Quill\Support\Helpers\Helpers;
use Quill\Support\Helpers\Path;
use Quill\Support\Pattern\Singleton;
use Quill\Support\Dot\Parser;

class Config extends Singleton
{
    private string $file;

    private array $items = [];

    protected function __construct(
        private readonly Parser $parser
    )
    {
        parent::__construct();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->parser->parse($key);

        $this->file($this->parser->first() . '.php');

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

    private function file(string $filename): self
    {
        $filename = Path::configFile($filename);

        if (!file_exists($filename)) {
            throw new LogicException('Please provide a valid config file');
        }

        $this->file = $filename;

        return $this;
    }
}
