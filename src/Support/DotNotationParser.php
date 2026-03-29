<?php

declare(strict_types=1);

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
        $notation = trim($notation);

        if ($notation === '') {
            return [];
        }

        $segments = array_map('trim', explode('.', $notation));

        return array_values(array_filter($segments, static fn(string $segment) => $segment !== ''));
    }
}
