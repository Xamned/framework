<?php

namespace framework\http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected string $scheme;
    protected string $authority;
    protected string $userInfo;
    protected string $host;
    protected int|null $port;
    protected string $path;
    protected string $query;
    protected string $fragment;

    public function __construct(
        protected string $uri = '',
    ) {
        if (empty($this->uri) === false) {
            $parts = parse_url($uri);
            $this->scheme = $parts['scheme'] ?? '';
            $this->host = $parts['host'] ?? '';
            $this->port = $parts['port'] ?? null;
            $this->path = $parts['path'] ?? '';
            $this->query = $parts['query'] ?? '';
            $this->fragment = $parts['fragment'] ?? '';
        }
    }

    public function getScheme(): string
    {
        return strtolower($this->scheme) ?? '';
    }

    public function getAuthority(): string
    {
        return $this->authority ?? '';
    }

    public function getUserInfo(): string
    {
        return $this->userInfo ?? '';
    }

    public function getHost(): string
    {
        return strtolower($this->host) ?? '';
    }

    public function getPort(): ?int
    {
        return $this->port ?? null;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment ?? '';
    }

    public function withScheme(string $scheme): UriInterface
    {
        $clone = clone $this;

        $clone->scheme = $scheme;

        return $clone;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $clone = clone $this;

        $userInfo = $password === null ? $user : "$user:$password";

        $clone->userInfo = $userInfo;

        return $clone;
    }

    public function withHost(string $host): UriInterface
    {
        $clone = clone $this;

        $clone->host = $host;

        return $clone;
    }

    public function withPort(?int $port): UriInterface
    {
        if ($port < 0 || 64738 < $port) {
            throw new InvalidArgumentException();
        }

        $clone = clone $this;

        $clone->port = $port;

        return $clone;
    }

    public function withPath(string $path): UriInterface
    {
        $clone = clone $this;

        $clone->path = $path;

        return $clone;
    }

    public function withQuery(string $query): UriInterface
    {
        $clone = clone $this;

        $clone->query = $query;

        return $clone;
    }

    public function withFragment(string $fragment): UriInterface
    {
        $clone = clone $this;

        $clone->fragment = $fragment;

        return $clone;
    }

    public function __toString(): string
    {
        return $this->uri;
    }
}
