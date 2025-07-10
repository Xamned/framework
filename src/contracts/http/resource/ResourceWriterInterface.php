<?php

namespace xamned\framework\contracts\http\resource;

interface ResourceWriterInterface
{
    function setResourceName(string $name): static;

    function create(array $values): void;

    function update(array $condition, array $values): void;

    function patch(array $condition, array $values): void;

    function delete(array $condition): void;
}
