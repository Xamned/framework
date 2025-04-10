<?php

namespace xamned\framework\db\file;

use xamned\framework\contracts\db\ListBuilderInterface;

abstract class AbstractListBuilder implements ListBuilderInterface
{
    protected array $data = [];

    public function __construct(
        public readonly string $file, 
        array $columns = []
    ) {
        $this->build($this->decode(), $columns);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getFileName(): string
    {
        return $this->file;
    }

    abstract public function encode(array $data): mixed;

    abstract protected function decode(): array;

    abstract protected function build(array $data, array $columns = []): void;
}
