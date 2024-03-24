<?php

declare(strict_types=1);

namespace App\Core\Config;

use App\Core\Patterns\Singleton;

class Config extends Singleton
{
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
