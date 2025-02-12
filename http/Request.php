<?php

namespace framework\http;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class Request extends Message implements RequestInterface
{
    public function __construct(
        protected string $protocolVersion,
        protected array $headers,
        protected StreamInterface $body,
        protected UriInterface $uri,
        protected string $requestTarget,
        protected string $method,
    ) {
        parent::__construct($protocolVersion, $headers, $body);
    }

    public function __clone()
    {
        $this->body = clone $this->body;
        $this->uri = clone $this->uri;
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget ?? $this?->uri->getPath() ?? '/';
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $clone = clone $this;

        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    public function getMethod(): string
    {
        return $this->method;
    }


    public function withMethod(string $method): RequestInterface
    {
        $clone = clone $this;

        $clone->method = $method;

        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $clone = clone $this;

        $clone->uri = $uri;

        if ($uri->getHost() === '') {
            return $clone;
        }

        if ($preserveHost === false || empty($clone->headers['Host']) === true) {
            $clone->headers['Host'] = $uri->getHost();
        }

        return $clone;
    }
}