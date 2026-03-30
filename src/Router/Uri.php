<?php

declare(strict_types=1);

namespace Quill\Router;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

final class Uri implements UriInterface
{
    private string $scheme = '';
    private string $userInfo = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        if ($uri === '') {
            return;
        }

        $parts = parse_url($uri);

        if ($parts === false) {
            $this->path = $uri;
            return;
        }

        $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
        $this->userInfo = $this->buildUserInfo($parts);
        $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $this->port = isset($parts['port']) ? (int) $parts['port'] : null;
        $this->path = $parts['path'] ?? '';
        $this->query = $parts['query'] ?? '';
        $this->fragment = $parts['fragment'] ?? '';
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function withScheme($scheme): UriInterface
    {
        $clone = clone $this;
        $clone->scheme = strtolower($scheme);

        return $clone;
    }

    public function getAuthority(): string
    {
        $authority = $this->host;

        if ($authority === '') {
            return '';
        }

        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        $port = $this->getPort();

        if ($port !== null) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function withUserInfo($user, $password = null): UriInterface
    {
        $clone = clone $this;
        $clone->userInfo = $password === null || $password === ''
            ? (string) $user
            : (string) $user . ':' . $password;

        return $clone;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function withHost($host): UriInterface
    {
        $clone = clone $this;
        $clone->host = strtolower($host);

        return $clone;
    }

    public function getPort(): ?int
    {
        if ($this->port === null) {
            return null;
        }

        if (($this->scheme === 'http' && $this->port === 80)
            || ($this->scheme === 'https' && $this->port === 443)
        ) {
            return null;
        }

        return $this->port;
    }

    public function withPort($port): UriInterface
    {
        if ($port !== null) {
            $port = (int) $port;

            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException('Port must be between 1 and 65535.');
            }
        }

        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function withPath($path): UriInterface
    {
        $this->assertNoQueryOrFragment($path);
        $clone = clone $this;
        $clone->path = (string) $path;

        return $clone;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function withQuery($query): UriInterface
    {
        $this->assertNoFragment($query);
        $clone = clone $this;
        $clone->query = ltrim((string) $query, '?');

        return $clone;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withFragment($fragment): UriInterface
    {
        $clone = clone $this;
        $clone->fragment = ltrim((string) $fragment, '#');

        return $clone;
    }

    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();

        if ($authority !== '') {
            $uri .= '//' . $authority;
        }

        $path = $this->path;

        if ($path !== '') {
            if ($authority !== '' && !str_starts_with($path, '/')) {
                $path = '/' . $path;
            }

            if ($authority === '' && str_starts_with($path, '//')) {
                $path = '/' . ltrim($path, '/');
            }

            $uri .= $path;
        }

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    private function buildUserInfo(array $parts): string
    {
        if (!isset($parts['user'])) {
            return '';
        }

        $user = $parts['user'];

        if (!isset($parts['pass']) || $parts['pass'] === '') {
            return $user;
        }

        return $user . ':' . $parts['pass'];
    }

    private function assertNoQueryOrFragment(string $path): void
    {
        if (str_contains($path, '?') || str_contains($path, '#')) {
            throw new InvalidArgumentException('Path cannot contain query string or fragment.');
        }
    }

    private function assertNoFragment(string $query): void
    {
        if (str_contains($query, '#')) {
            throw new InvalidArgumentException('Query string cannot contain a fragment.');
        }
    }
}
