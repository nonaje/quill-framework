<?php

declare(strict_types=1);

namespace Quill\Config;

use Quill\Support\Pattern\Singleton;

class Config extends Singleton
{
    public const string DEFAULT_CONFIG_PATH = __DIR__ . '/../../../config';

    protected function __construct(
        private readonly Parser $parser
    ) {
        parent::__construct();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->parser->parse($key);

        if ($tree = $this->parser->tree()) {
            foreach ($tree as $pointer) {
                $value = $this->items[$pointer] ?? $value[$pointer] ?? null;
            }
        }

        return $value ?? $default;
    }
}
