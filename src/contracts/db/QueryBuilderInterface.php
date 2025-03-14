<?php

namespace xamned\framework\contracts\db;

interface QueryBuilderInterface
{
    function select(array|string ...$fields): static;

    function from(array|string $resource): static;

    function where(array $condition): static;

    function whereIn(string $column, array $values): static;

    function join(string $type, string|array $resource, string $on): static;

    function orderBy(array $columns): static;

    function limit(int $limit): static;

    function offset(int $offset): static;
}
