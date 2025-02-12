<?php

namespace framework\http;

use framework\http\factories\StreamFactory;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

class UploadedFile implements UploadedFileInterface
{
    public function __construct(
        protected StreamInterface $stream,
        protected ?int $size,
        protected int $error,
        protected ?string $filename,
        protected ?string $mediaType,
    ) {
    }

    public function getStream(): StreamInterface
    {
        if (empty($this->stream) === true) {
            $this->stream = (new StreamFactory())->createStreamFromFile($this->filename);
        }
        return $this->stream;
    }
 
    public function moveTo(string $targetPath): void
    {
        $this->stream?->close();

        move_uploaded_file($this->filename, $targetPath) ?: rename($this->filename, $targetPath);

        $this->stream = (new StreamFactory())->createStreamFromFile($targetPath);
    }
    
    public function getSize(): ?int
    {
        return $this->size;
    }
    
    public function getError(): int
    {
        return $this->error;
    }
    
    public function getClientFilename(): ?string
    {
        return $this->filename;
    }
    
    public function getClientMediaType(): ?string
    {
        return $this->mediaType;
    }
}