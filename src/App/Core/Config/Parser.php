<?php

declare(strict_types=1);

namespace App\Core\Config;

class Parser
{
    public const string CONFIG_PATH = __DIR__ . '/../../../config';

    private array $tree = [];

    public function parse(string $key): mixed
    {
        $this->tree = [];

        $exploded = explode('.', $key, 2);
        $file = self::CONFIG_PATH . "/$exploded[0].php";

        if (count($exploded) === 1) {
            if (file_exists($file)) {
                return require $file;
            } else {
                throw new \LogicException('Please provide a valid config file.');
            }
        }

        $this->tree = explode('.',  $exploded[1]);

        return file_exists($file) ? require $file : null;
    }

    public function tree(): array
    {
        return $this->tree;
    }
}
