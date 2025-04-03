<?php

namespace xamned\framework\contracts\db;

interface ListBuilderInterface
{
    function getData(): array;

    function getFileName(): string;

    function encode(array $data): mixed;
}
