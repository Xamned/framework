<?php

namespace framework\http;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    public function __construct(
        protected string $protocolVersion,
        protected array $headers,
        protected StreamInterface $body,
        protected UriInterface $uri,
        protected string $requestTarget,
        protected string $method,
        protected array $serverParams,
        protected array $cookieParams,
        protected array $queryParams,
        protected array $uploadedFiles,
        protected array|object|null $parsedBody,
        protected array $attributes,
    ) {
        parent::__construct(
            $protocolVersion, 
            $headers, 
            $body,
            $uri,
            $requestTarget,
            $method,
        );
    }

    public function __clone()
    {
        parent::__clone();

        if (is_object($this->parsedBody) === true) {
            $this->parsedBody = clone $this->parsedBody;
        }
    }
    
    public function getServerParams(): array
    {
        return $this->serverParams;
    }
     
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }
     
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;

        $clone->cookieParams = $cookies;

        return $clone;
    }
     
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
     
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;

        $clone->queryParams = $query;

        return $clone;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $clone = clone $this;

        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }
     
    public function getParsedBody(): array|object|null
    {
        return $this->parsedBody;
    }
     
    public function withParsedBody($data): ServerRequestInterface
    {
        $clone = clone $this;

        $clone->parsedBody = $data;

        return $clone;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }
          
    public function withAttribute(string $name, mixed $value): ServerRequestInterface
    {
        $clone = clone $this;

        $clone->attributes[$name] = $value;

        return $clone;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $clone = clone $this;

        unset($clone->attributes[$name]);

        return $clone;
    }
}