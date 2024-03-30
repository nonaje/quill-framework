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

    public function load(array $files): void
    {
        foreach ($files as $filename) {
            $this->firstLoad($filename);
        }
    }

    private function searchInItems(): mixed
    {
        $value = null;

        if ($this->parser->count() === 1) {
            return $this->items[$this->parser->first()];
        }

        foreach (array_slice($this->parser->list(), 1) as $pointer) {
            $value = $this->items[$this->parser->first()][$pointer] ?? $value[$pointer] ?? null;
        }

        return $value;
    }

    private function firstLoad(string $filename): void
    {
        if ($filename === 'env') {
            $this->loadDotEnv();
            return;
        }

        $this->items[$filename] = require_once Path::configFile($filename . '.php');
    }

    private function loadDotEnv(): void
    {
        $env = [];

        foreach (parse_ini_file(Path::applicationFile('.env')) as $key => $value) {
            $env[strtolower($key)] = $value;
        }

        $this->items['env'] = $env;
        unset($env);
    }
}
