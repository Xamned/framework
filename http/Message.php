<?php

namespace framework\http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    public function __construct(
        protected string $protocolVersion,
        protected array $headers,
        protected StreamInterface $body,
    ) {
    }

    public function __clone()
    {
        $this->body = clone $this->body;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $clone = clone $this;

        $clone->protocolVersion = $version;

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        foreach (array_keys($this->headers) as $header) {
            if (strcasecmp($header, $name) === 0) {
                return true;
            }
        }

        return false;
    }

    public function getHeader(string $name): array
    {
        foreach (array_keys($this->headers) as $header) {
            if (strcasecmp($header, $name) === 0) {
                return $this->headers[$header];
            }
        }

        return [];
    }

    public function getHeaderLine(string $name, string $separator = ','): string
    {
        $header = $this->getHeader($name);

        return $header !== [] 
            ? implode($separator, $header)
            : '';
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $clone = clone $this;

        $clone->headers[$name] = is_array($value) === true 
            ? $value 
            : [$value];

        return $clone;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $clone = clone $this;

        if (is_array($value) === true) {
            $clone->headers[$name] = array_merge($clone->getHeader($name), $value);
            return $clone;
        }

        $clone->headers[$name][] = $value;

        return $clone;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $clone = clone $this;

        foreach (array_keys($this->headers) as $header) {
            if (strcasecmp($header, $name) === 0) {
                unset($clone->headers[$header]);
            }
        }

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone = clone $this;

        $clone->body = $body;

        return $clone;
    }
}
