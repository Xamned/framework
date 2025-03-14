<?php

namespace xamned\framework\contracts\http\resource;

interface ResourceWriterInterface
{
    function setResourceName(string $name): static;

    function create(array $values): void;

    function update(string|int $id, array $values): void;

    function patch(string|int $id, array $values): void;

    function delete(string|int $id): void;
}
