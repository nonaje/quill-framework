<?php

namespace Quill\Contracts\Support;

interface DotNotationParserInterface
{
    /**
     * Convert a delimiter separated values (dsv) into an one-dimensional array
     *
     * @param string $key
     * @param string $separator
     * @return DotNotationParserInterface
     */
    public function parse(string $key, string $separator = '.'): self;

    /**
     * Return the given dsv to parsing
     *
     * @return string
     */
    public function key(): string;

    /**
     * Return the parsed dsv to a one-dimensional array
     *
     * @return array
     */
    public function list(): array;

    /**
     * Return the first position of the parsed dsv
     *
     * @return string
     */
    public function first(): string;

    /**
     * Return the last position of the parsed dsv
     *
     * @return string
     */
    public function last(): string;

    /**
     * Return the number of elements in the parsed dsv
     *
     * @return int
     */
    public function count(): int;

}