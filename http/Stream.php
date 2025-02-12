<?php

namespace framework\http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    public function __construct(
        protected $resource,
        private string $context = 'asd',
    ) {
        if (empty($this->context) === false) {
            fwrite($this->resource, $this->context);
        }
    }

    public function __toString(): string
    {
        try {
            $this->rewind();
            return stream_get_contents($this->resource) ?? '';
        } catch (RuntimeException $e) {
            return '';
        }
    }

    public function close(): void
    {
        fclose($this->resource);
        $this->resource = null;
    }

    public function detach()
    {
        $resource = $this->resource;

        $this->resource = null;
        
        return $resource;
    }

    public function getSize(): ?int
    {
        return filesize($this->resource) ?? null;
    }

    public function tell(): int
    {
        return ftell($this->resource);
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        return stream_get_meta_data($this->resource)['seekable'];
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        fseek($this->resource, $offset, $whence);
    }

    public function rewind(): void
    {
        rewind($this->resource);
    }

    public function isWritable(): bool
    {
        return is_writable($this->resource);
    }

    public function write(string $string): int
    {
        return (int) fwrite($this->resource, $string);
    }

    public function isReadable(): bool
    {
        return is_readable($this->resource);
    }

    public function read(int $length): string
    {
        return fread($this->resource, $length) ?? '';
    }

    public function getContents(): string
    {
        return fpassthru($this->resource);
    }

    public function getMetadata(?string $key = null)
    {
        return $key === null 
            ? stream_get_meta_data($this->resource) 
            : stream_get_meta_data($this->resource)[$key] ?? null;
    }
}