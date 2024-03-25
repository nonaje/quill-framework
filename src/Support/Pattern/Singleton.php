<?php

declare(strict_types=1);

namespace Quill\Support\Pattern;

use Exception;

abstract class Singleton
{
    private static array $instance = [];

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::make() instead
     */
    protected function __construct()
    {
    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function make(...$params): static
    {
        $class = get_called_class();

        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new $class(...$params);
        }

        return self::$instance[$class];
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}