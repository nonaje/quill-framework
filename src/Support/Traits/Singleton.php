<?php

namespace Quill\Support\Traits;

use Exception;

trait Singleton
{
    protected static null|self $_instance = null;

    /**
     * Protected class constructor to prevent direct object creation.
     */
    protected function  __construct() { }

    /**
     * Prevent object cloning
     */
    final protected function  __clone() { }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     * @throws Exception
     */
    final public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * To return new or existing Singleton instance of the class from which it is called.
     * As it sets to final it can't be overridden.
     *
     * @return static Singleton instance of the class.
     */
    final public static function make(...$params): static
    {
        $class = get_called_class();

        if (static::$_instance === null) {
            static::$_instance = new $class(...$params);
        }

        return static::$_instance;
    }
}
