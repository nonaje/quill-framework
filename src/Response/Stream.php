<?php

declare(strict_types=1);

namespace Quill\Response;

use Psr\Http\Message\StreamInterface;

final class Stream implements StreamInterface
{
    /** @var resource|null */
    private $resource;

    private function __construct($resource)
    {
        $this->resource = $resource;
    }

    public static function create(string $content = ''): self
    {
        $resource = fopen('php://temp', 'rw+');

        if ($content !== '') {
            fwrite($resource, $content);
            rewind($resource);
        }

        return new self($resource);
    }

    public function __toString(): string
    {
        try {
            if ($this->resource === null) {
                return '';
            }

            $this->seek(0);
            return stream_get_contents($this->resource) ?: '';
        } catch (\Throwable) {
            return '';
        }
    }

    public function close(): void
    {
        if ($this->resource !== null) {
            fclose($this->resource);
            $this->resource = null;
        }
    }

    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;

        return $resource;
    }

    public function getSize(): ?int
    {
        if ($this->resource === null) {
            return null;
        }

        $stats = fstat($this->resource);

        return $stats['size'] ?? null;
    }

    public function tell(): int
    {
        if ($this->resource === null) {
            throw new \RuntimeException('No resource available.');
        }

        $position = ftell($this->resource);

        if ($position === false) {
            throw new \RuntimeException('Unable to determine stream position.');
        }

        return $position;
    }

    public function eof(): bool
    {
        return $this->resource === null || feof($this->resource);
    }

    public function isSeekable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);

        return $meta['seekable'] ?? false;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable.');
        }

        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek in stream.');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $mode = stream_get_meta_data($this->resource)['mode'] ?? '';

        return strpbrk($mode, 'waxc+') !== false;
    }

    public function write($string): int
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable.');
        }

        $result = fwrite($this->resource, (string) $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream.');
        }

        return $result;
    }

    public function isReadable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $mode = stream_get_meta_data($this->resource)['mode'] ?? '';

        return strpbrk($mode, 'r+') !== false;
    }

    public function read($length): string
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable.');
        }

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream.');
        }

        return $result;
    }

    public function getContents(): string
    {
        if ($this->resource === null) {
            throw new \RuntimeException('No resource available.');
        }

        $result = stream_get_contents($this->resource);

        if ($result === false) {
            throw new \RuntimeException('Unable to read stream contents.');
        }

        return $result;
    }

    public function getMetadata($key = null): mixed
    {
        if ($this->resource === null) {
            return $key === null ? [] : null;
        }

        $meta = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }
}
