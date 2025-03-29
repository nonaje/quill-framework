<?php

namespace Quill\Support;

/**
 * It provides a method to convert a dot notation string into a one-dimensional array.
 */
trait DotNotationParser
{
    /**
     * Converts a dot notation string into an array.
     *
     * @param string $notation .
     * @return array.
     */
    protected function dotNotationToArray(string $notation): array
    {
        return array_values(
            array_filter(explode('.', strtolower($notation)))
        );
    }
}
